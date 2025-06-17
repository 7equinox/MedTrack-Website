<?php
require_once __DIR__ . '/../../config/database.php';

// --- Search and Pagination Logic ---
$search_term = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = 10;
$offset = ($page - 1) * $rows_per_page;
$search_query = "%" . $search_term . "%";

// --- Get total number of records for pagination ---
$total_rows_sql = "
    SELECT COUNT(*) as total
    FROM medicationschedule m
    INNER JOIN patients p ON m.PatientID = p.PatientID
    WHERE p.IsArchived = FALSE AND m.Status != 'Taken' AND (m.PatientID LIKE ? OR p.PatientName LIKE ? OR m.MedicationName LIKE ? OR p.RoomNumber LIKE ?)
";
$stmt_total = $conn->prepare($total_rows_sql);
$stmt_total->bind_param("ssss", $search_query, $search_query, $search_query, $search_query);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $rows_per_page);

// --- Fetch medication data ---
$sql = "
    SELECT 
        m.PatientID, p.PatientName, m.MedicationName, m.Dosage, m.MedicationFor, 
        m.Frequency, m.Duration, m.DurationUnit, m.IntakeTime, p.RoomNumber,
        ROW_NUMBER() OVER (PARTITION BY m.PrescriptionGUID ORDER BY m.IntakeTime ASC) as DoseNumber,
        (m.Frequency * (CASE WHEN m.DurationUnit = 'weeks' THEN m.Duration * 7 ELSE m.Duration END)) as TotalDoses
    FROM medicationschedule m
    INNER JOIN patients p ON m.PatientID = p.PatientID
    WHERE p.IsArchived = FALSE AND m.Status != 'Taken' AND (m.PatientID LIKE ? OR p.PatientName LIKE ? OR m.MedicationName LIKE ? OR p.RoomNumber LIKE ?)
    ORDER BY m.IntakeTime
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $search_query, $search_query, $search_query, $search_query, $rows_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// --- Generate Table Body HTML ---
$table_body_html = '';
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $table_body_html .= '<tr>';
        $table_body_html .= '<td>' . htmlspecialchars($row['PatientID']) . '</td>';
        $table_body_html .= '<td>' . htmlspecialchars($row['PatientName']) . '</td>';
        $table_body_html .= '<td>' . htmlspecialchars($row['MedicationName']) . '</td>';
        $table_body_html .= '<td>' . htmlspecialchars($row['Dosage']) . '</td>';
        $table_body_html .= '<td>' . htmlspecialchars($row['MedicationFor']) . '</td>';
        $table_body_html .= '<td>' . htmlspecialchars($row['Frequency']) . 'x per day</td>';
        $unit = htmlspecialchars($row['DurationUnit']);
        $displayUnit = ($unit === 'weeks') ? 'week(s)' : (($unit === 'days') ? 'day(s)' : $unit);
        $table_body_html .= '<td>' . htmlspecialchars($row['Duration']) . ' ' . $displayUnit . '</td>';
        $table_body_html .= '<td>' . htmlspecialchars($row['DoseNumber']) . ' / ' . htmlspecialchars($row['TotalDoses']) . '</td>';
        $table_body_html .= '<td>' . htmlspecialchars(date('Y-m-d h:i A', strtotime($row['IntakeTime']))) . '</td>';
        $table_body_html .= '<td>' . htmlspecialchars($row['RoomNumber']) . '</td>';
        $table_body_html .= '</tr>';
    }
} else {
    $table_body_html = '<tr><td colspan="10">No medication schedules found.</td></tr>';
}

// --- Generate Pagination HTML ---
$pagination_html = '';
$search_param = !empty($search_term) ? '&search=' . urlencode($search_term) : '';

if ($total_pages > 0) {
    $pagination_html .= '<a href="?page=' . max(1, $page - 1) . $search_param . '" data-page="' . max(1, $page - 1) . '" class="btn-page ' . ($page <= 1 ? 'disabled' : '') . '">&laquo; Previous</a>';

    for ($i = 1; $i <= $total_pages; $i++) {
        $pagination_html .= '<a href="?page=' . $i . $search_param . '" data-page="' . $i . '" class="btn-page ' . ($page == $i ? 'active' : '') . '">' . $i . '</a>';
    }

    $pagination_html .= '<a href="?page=' . min($total_pages, $page + 1) . $search_param . '" data-page="' . min($total_pages, $page + 1) . '" class="btn-page ' . ($page >= $total_pages ? 'disabled' : '') . '">Next &raquo;</a>';
}

// --- Return JSON response ---
header('Content-Type: application/json');
echo json_encode([
    'tableBody' => $table_body_html,
    'pagination' => $pagination_html
]); 