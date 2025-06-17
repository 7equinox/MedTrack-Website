<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

$page_title = 'Personnel Management';
$body_class = 'page-personnel-patient-list'; // Re-use styling
$base_path = '../..';
$activePage = 'personnel';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/admin_header.php';

// Fetch all personnel
$sql = "SELECT PersonnelID, PersonnelName, Email, IsArchived FROM personnel ORDER BY IsArchived, PersonnelName";
$personnel_list = $conn->query($sql);
?>

<main>
    <h1 class="page-title">Personnel Management</h1>

    <div class="list-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <input id="search-input" type="text" placeholder="Search by ID, Name or Email..." class="search-bar-input" />
            <a href="personnel_add.php" class="btn btn-add">+ Add Personnel</a>
        </div>

        <div class="table-wrapper">
            <table id="personnel-table">
                <thead>
                    <tr>
                        <th>Personnel ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($personnel_list && $personnel_list->num_rows): ?>
                        <?php while ($p = $personnel_list->fetch_assoc()): ?>
                            <tr class="<?= $p['IsArchived'] ? 'archived' : '' ?>">
                                <td><?= htmlspecialchars($p['PersonnelID']) ?></td>
                                <td><?= htmlspecialchars($p['PersonnelName']) ?></td>
                                <td><?= htmlspecialchars($p['Email']) ?></td>
                                <td><span class="status-badge <?= $p['IsArchived'] ? 'status-archived' : 'status-active' ?>"><?= $p['IsArchived'] ? 'Archived' : 'Active' ?></span></td>
                                <td class="action-cell">
                                    <a href="personnel_edit.php?id=<?= urlencode($p['PersonnelID']) ?>" class="btn btn-edit">Edit</a>
                                    <?php if ($p['IsArchived']): ?>
                                        <a href="personnel_archive.php?action=restore&id=<?= urlencode($p['PersonnelID']) ?>" class="btn btn-restore">Restore</a>
                                    <?php else: ?>
                                        <a href="personnel_archive.php?action=archive&id=<?= urlencode($p['PersonnelID']) ?>" class="btn btn-archive"><i class="fas fa-archive"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No personnel found.</td></tr>
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
    const table = document.getElementById('personnel-table');
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