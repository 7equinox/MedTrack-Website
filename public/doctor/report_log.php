<?php
session_start();
if (!isset($_SESSION['DoctorID']) || !isset($_SESSION['DoctorName'])) {
    header("Location: ../../doctor/doctor_login.php");
    exit();
}
$DoctorID = $_SESSION['DoctorID'];
$DoctorName = $_SESSION['DoctorName'];

require_once __DIR__ . '/../../config/database.php';

$page_title = 'Medication Adherence Reports';
$body_class = 'page-doctor-report-log';
$base_path = '../..';
$activePage = 'report_log';
require_once __DIR__ . '/../../templates/partials/doctor_header.php';

// Fetch reports only created by the logged-in doctor that are NOT resolved
$reportQuery = "
    SELECT r.ReportID, r.PatientID, r.DoctorID, r.ReportDetails, r.ReportStatus, r.ReportDate, p.PatientName
    FROM reports r
    JOIN patients p ON r.PatientID = p.PatientID
    WHERE r.DoctorID = ? AND r.ReportStatus != 'Resolved'
    ORDER BY r.ReportDate DESC
";

$stmt = $conn->prepare($reportQuery);
$stmt->bind_param("s", $DoctorID);
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
                        <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">
                            <?= date('M d, Y, h:i A', strtotime($report['ReportDate'])) ?>
                        </p>
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
require_once __DIR__ . '/../../templates/partials/doctor_side_menu.php';
require_once __DIR__ . '/../../templates/partials/doctor_footer.php'; 
?>
