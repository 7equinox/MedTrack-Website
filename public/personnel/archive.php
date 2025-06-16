<?php
session_start();
if (!isset($_SESSION['PersonnelID'])) {
    header("Location: ../../personnel/personnel_login.php");
    exit();
}

$page_title = 'Archived Patients';
$body_class = 'page-personnel-archive';
$base_path = '../..';
$activePage = 'patient_list';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/personnel_header.php';

// Fetch archived patients
$sql = "SELECT PatientID, PatientName, RoomNumber 
        FROM patients 
        WHERE IsArchived = 1 
        ORDER BY PatientName";
$result = $conn->query($sql);
?>

<main>
  <div class="archive-header">
    <h1 class="archive-title">Archived Patients</h1>
    <a href="patient_list.php" class="btn back-btn">← Back to Patient List</a>
  </div>

  <div class="list-container">
    <div class="search-bar">
      <input id="search-input" type="text" placeholder="Search archived patients..." class="search-bar-input" />
    </div>
    <div class="table-wrapper">
      <table id="archive-table">
        <thead>
          <tr>
            <th>Patient ID</th>
            <th>Patient Name</th>
            <th>Room No.</th>
            <th style="width: 150px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows): ?>
            <?php while ($p = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($p['PatientID']) ?></td>
                <td><?= htmlspecialchars($p['PatientName']) ?></td>
                <td><?= htmlspecialchars($p['RoomNumber']) ?></td>
                <td>
                  <button class="btn btn-restore" onclick="restorePatient('<?= htmlspecialchars($p['PatientID']) ?>')">
                    Restore
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" style="text-align: center;">No archived patients found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
// Live search
const input = document.getElementById('search-input');
const table = document.getElementById('archive-table');
input.addEventListener('input', () => {
  const filter = input.value.toLowerCase();
  Array.from(table.tBodies[0].rows).forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
  });
});

// Restore button
function restorePatient(pid) {
  if (!confirm(`Restore patient ${pid}?`)) return;

  fetch(`restore_patient.php?id=${encodeURIComponent(pid)}`)
    .then(res => {
      if (res.ok) {
        location.reload();
      } else {
        return res.text().then(txt => { throw new Error(txt || 'Unknown error occurred.') });
      }
    })
    .catch(err => alert('Error restoring patient: ' + err.message));
}
</script>

<?php
require_once __DIR__ . '/../../templates/partials/personnel_side_menu.php';
require_once __DIR__ . '/../../templates/partials/personnel_footer.php';
?>
