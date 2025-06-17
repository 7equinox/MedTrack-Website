<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

$page_title = 'Doctor Management';
$body_class = 'page-doctor-patient-list'; // Re-use styling
$base_path = '../..';
$activePage = 'doctor';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/admin_header.php';

// Fetch all doctor
$sql = "SELECT DoctorID, DoctorName, Email, IsArchived FROM doctor ORDER BY IsArchived, DoctorName";
$doctor_list = $conn->query($sql);
?>

<main>
    <h1 class="page-title">Doctor Management</h1>

    <div class="list-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <input id="search-input" type="text" placeholder="Search by ID, Name or Email..." class="search-bar-input" />
            <a href="doctor_add.php" class="btn btn-add">+ Add Doctor</a>
        </div>

        <div class="table-wrapper">
            <table id="doctor-table">
                <thead>
                    <tr>
                        <th>Doctor ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($doctor_list && $doctor_list->num_rows): ?>
                        <?php while ($p = $doctor_list->fetch_assoc()): ?>
                            <tr class="<?= $p['IsArchived'] ? 'archived' : '' ?>">
                                <td><?= htmlspecialchars($p['DoctorID']) ?></td>
                                <td><?= htmlspecialchars($p['DoctorName']) ?></td>
                                <td><?= htmlspecialchars($p['Email']) ?></td>
                                <td><span class="status-badge <?= $p['IsArchived'] ? 'status-archived' : 'status-active' ?>"><?= $p['IsArchived'] ? 'Archived' : 'Active' ?></span></td>
                                <td class="action-cell">
                                    <a href="doctor_edit.php?id=<?= urlencode($p['DoctorID']) ?>" class="btn btn-edit">Edit</a>
                                    <?php if ($p['IsArchived']): ?>
                                        <a href="doctor_archive.php?action=restore&id=<?= urlencode($p['DoctorID']) ?>" class="btn btn-restore">Restore</a>
                                    <?php else: ?>
                                        <a href="doctor_archive.php?action=archive&id=<?= urlencode($p['DoctorID']) ?>" class="btn btn-archive"><i class="fas fa-archive"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No doctor found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<style>
.status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; }
.status-active { background-color: #d4edda; color: #155724; }
.status-archived { background-color: #f8d7da; color: #721c24; }
tr.archived { background-color: #f1f1f1; opacity: 0.7; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live Search for table
    const searchInput = document.getElementById('search-input');
    const table = document.getElementById('doctor-table');
    searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase();
        for (let row of table.tBodies[0].rows) {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        }
    });
});
</script>

<?php
require_once __DIR__ . '/../../templates/partials/admin_side_menu.php';
require_once __DIR__ . '/../../templates/partials/admin_footer.php';
?> 