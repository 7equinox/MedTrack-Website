<?php
$page_title = 'Patient Management List';
$body_class = 'page-staff-patient-list';
$base_path = '../..';
$activePage = 'patient_list';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/staff_header.php';

// Fetch non-archived patients
$sql = "SELECT PatientID, PatientName, RoomNumber FROM patients WHERE IsArchived = FALSE ORDER BY PatientName";
$patients = $conn->query($sql);
?>

<main>
  <h1 class="page-title">Patient Management</h1>

  <div class="list-container">
    <!-- Top Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <input id="search-input" type="text" placeholder="Search ID, Name or Room..." class="search-bar-input" />
      <button id="toggleAddForm" class="btn btn-add">+ Add Patient</button>
    </div>

    <!-- Add Patient Form -->
    <div id="addPatientContainer" class="add-med-form hidden" style="margin-bottom: 20px;">
        <h3 class="form-title">Add New Patient</h3>
        <form action="add_patient.php" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="PatientID">Patient ID</label>
                    <input type="text" id="PatientID" name="PatientID" placeholder="PT-XXXX" required>
                </div>
                <div class="form-group">
                    <label for="PatientName">Patient Name</label>
                    <input type="text" id="PatientName" name="PatientName" required>
                </div>
                <div class="form-group">
                    <label for="Birthdate">Birthdate</label>
                    <input type="date" id="Birthdate" name="Birthdate" required>
                </div>
                <div class="form-group">
                    <label for="Sex">Sex</label>
                    <select id="Sex" name="Sex" required>
                        <option value="" disabled selected>Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group col-span-2">
                    <label for="HomeAddress">Home Address</label>
                    <input type="text" id="HomeAddress" name="HomeAddress">
                </div>
                <div class="form-group">
                    <label for="Email">Email</label>
                    <input type="email" id="Email" name="Email">
                </div>
                <div class="form-group">
                    <label for="ContactNumber">Contact Number</label>
                    <input type="text" id="ContactNumber" name="ContactNumber">
                </div>
                <div class="form-group">
                    <label for="RoomNumber">Room Number</label>
                    <input type="text" id="RoomNumber" name="RoomNumber">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" id="cancelAddPatient" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-save">Save Patient</button>
            </div>
        </form>
    </div>

    <!-- Patient Table -->
    <div class="table-wrapper">
      <table id="patient-table">
        <thead>
          <tr>
            <th>Patient ID</th>
            <th>Patient Name</th>
            <th>Room No.</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($patients && $patients->num_rows): ?>
            <?php while ($p = $patients->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($p['PatientID']) ?></td>
                <td><?= htmlspecialchars($p['PatientName']) ?></td>
                <td><?= htmlspecialchars($p['RoomNumber']) ?></td>
                <td class="action-cell">
                  <a href="<?= $base_path ?>/public/staff/patient_med_history.php?id=<?= urlencode($p['PatientID']) ?>" class="btn btn-view">View</a>
                  <a href="<?= $base_path ?>/public/staff/patient_edit.php?id=<?= urlencode($p['PatientID']) ?>" class="btn btn-edit">Edit</a>
                  <button class="btn btn-archive" onclick="archivePatient('<?= $p['PatientID'] ?>')">
                    <i class="fas fa-archive"></i>
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center">No patients found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
  // Toggle Add Patient Form
  const toggleBtn = document.getElementById('toggleAddForm');
  const formContainer = document.getElementById('addPatientContainer');
  const cancelBtn = document.getElementById('cancelAddPatient');

  if (toggleBtn && formContainer && cancelBtn) {
    toggleBtn.addEventListener('click', function () {
      formContainer.classList.remove('hidden');
      toggleBtn.disabled = true;
    });

    cancelBtn.addEventListener('click', function () {
      formContainer.classList.add('hidden');
      toggleBtn.disabled = false;
      // Optional: Reset form fields on cancel
      formContainer.querySelector('form').reset();
    });
  }

  // Live Search
  const input = document.getElementById('search-input');
  const table = document.getElementById('patient-table');
  input.addEventListener('input', () => {
    const filter = input.value.toLowerCase();
    for (let row of table.tBodies[0].rows) {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(filter) ? '' : 'none';
    }
  });

  // Archive Patient
  function archivePatient(pid) {
    if (confirm(`Archive patient: ${pid}? This will deactivate their profile.`)) {
      fetch(`archive_patient.php?id=${encodeURIComponent(pid)}`)
        .then(res => {
          if (res.ok) {
            location.reload();
          } else {
            alert('Archive failed.');
          }
        })
        .catch(() => alert('Request error.'));
    }
  }
</script>

<?php
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php';
?>
