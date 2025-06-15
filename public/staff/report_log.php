<?php
session_start();
if (!isset($_SESSION['StaffID']) || !isset($_SESSION['StaffName'])) {
    header("Location: ../../staff/staff_login.php");
    exit();
}
$staffID = $_SESSION['StaffID'];
$staffName = $_SESSION['StaffName'];

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Medication Adherence Reports';
$body_class = 'page-staff-report-log';
$base_path = '../..';
$activePage = 'report_log';
require_once __DIR__ . '/../../templates/partials/staff_header.php';

// Fetch reports only created by the logged-in staff
$reportQuery = "
    SELECT r.ReportID, r.PatientID, r.StaffID, r.ReportDetails, r.ReportStatus, p.PatientName
    FROM reports r
    JOIN patients p ON r.PatientID = p.PatientID
    WHERE r.StaffID = ?
    ORDER BY r.ReportID DESC
";

$stmt = $conn->prepare($reportQuery);
$stmt->bind_param("s", $staffID);
$stmt->execute();
$reports = $stmt->get_result();
?>

<main>
    <div class="reports-header">
        <h1 class="reports-title">Medication Adherence Reports</h1>
        <a href="dashboard.php" class="back-btn">Go Back to Dashboard</a>
    </div>

    <div class="reports-list">
        <?php if ($reports && $reports->num_rows > 0): ?>
            <?php while ($report = $reports->fetch_assoc()): ?>
                <div class="report-card">
                    <div>
                        <h2>Report No. <?= htmlspecialchars($report['ReportID']) ?></h2>
                        <p>[<?= htmlspecialchars($report['PatientID']) ?>] <?= htmlspecialchars($report['PatientName']) ?></p>
                        <p class="report-snippet"><?= nl2br(htmlspecialchars(substr($report['ReportDetails'], 0, 100))) ?>...</p>
                    </div>
                    <a href="access_report_log.php?report=<?= $report['ReportID'] ?>" class="status-btn status-<?= strtolower($report['ReportStatus']) ?>">
                        <?= htmlspecialchars($report['ReportStatus']) ?>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="padding: 1rem; color: gray;">No reports found for your account.</p>
        <?php endif; ?>
    </div>
</main>

<?php 
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php'; 
?>
