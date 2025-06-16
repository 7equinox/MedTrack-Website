<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$page_title = 'Access Report Log';
$body_class = 'page-staff-access-report-log';
$base_path = '../..';
$activePage = 'report_log';

$reportID = $_GET['report'] ?? null;

if (!$reportID) {
    header("Location: report_log.php");
    exit();
}

// Fetch report
$stmt = $conn->prepare("
    SELECT r.ReportID, r.PatientID, r.StaffID, r.ReportDetails, r.ReportStatus,
           p.PatientName, p.RoomNumber, s.StaffName
    FROM reports r
    JOIN patients p ON r.PatientID = p.PatientID
    LEFT JOIN staff s ON r.StaffID = s.StaffID
    WHERE r.ReportID = ?
");
$stmt->bind_param("i", $reportID);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    echo "<main><p style='padding:2rem;'>Report not found.</p></main>";
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $updateStmt = $conn->prepare("UPDATE reports SET ReportStatus = ? WHERE ReportID = ?");
    $updateStmt->bind_param("si", $newStatus, $reportID);
    $updateStmt->execute();
    header("Location: access_report_log.php?report=" . $reportID . "&update_success=1");
    exit();
}

require_once __DIR__ . '/../../templates/partials/staff_header.php';
?>

<main style="max-width: 900px; margin: auto; padding: 2rem;">
    <?php if (isset($_GET['update_success'])): ?>
        <div class="alert success" id="success-panel" style="margin-bottom: 1.5rem; background-color: #d4edda; color: #155724; padding: 1rem; border: 1px solid #c3e6cb; border-radius: 8px;">
            Report status has been successfully updated.
        </div>
    <?php endif; ?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h1 style="font-size:2rem; font-weight:600;">Report #<?= htmlspecialchars($report['ReportID']) ?></h1>
        <a href="report_log.php" style="text-decoration:none; background:#e0e0e0; padding:10px 18px; border-radius:6px; color:#333;">← Back</a>
    </div>

    <form method="POST" style="background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; flex-direction:column; gap:1.5rem;">
        <div>
            <p><strong>From:</strong> <?= htmlspecialchars($report['StaffName'] ?? 'System') ?></p>
            <p><strong>Patient:</strong> <?= htmlspecialchars($report['PatientName']) ?> <span style="color:gray;">(Room <?= htmlspecialchars($report['RoomNumber']) ?>)</span></p>
        </div>

        <div style="background:#f9f9f9; border:1px solid #ccc; padding:1rem; border-radius:8px;">
            <?= nl2br(htmlspecialchars($report['ReportDetails'])) ?>
        </div>

        <div>
            <label style="font-weight:600;">Update Report Status:</label>
            <div style="display:flex; gap:1rem; margin-top:0.5rem;">
                <?php
                $statuses = ['Inspect' => '#ffc107', 'Ongoing' => '#17a2b8', 'Resolved' => '#28a745'];
                foreach ($statuses as $label => $color):
                    $checked = ($report['ReportStatus'] === $label) ? 'checked' : '';
                    $activeStyle = $checked ? "background: $color; color: white;" : "background: #f0f0f0; color: #333;";
                ?>
                    <label style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem; border-radius:8px; cursor:pointer; <?= $activeStyle ?>">
                        <input type="radio" name="status" value="<?= $label ?>" <?= $checked ?> style="accent-color: <?= $color ?>;">
                        <?= $label ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" style="align-self:flex-start; background:#007bff; color:white; border:none; padding:10px 20px; border-radius:6px; font-size:1rem; cursor:pointer;">
            Update Status
        </button>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const successPanel = document.getElementById('success-panel');
    if (successPanel) {
        setTimeout(() => {
            successPanel.style.transition = 'opacity 0.5s ease';
            successPanel.style.opacity = '0';
            setTimeout(() => successPanel.style.display = 'none', 500);
        }, 3000);
    }
});
</script>

<?php 
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php'; 
?>
