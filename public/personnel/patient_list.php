<?php
$page_title = 'Patient Management List';
$body_class = 'page-personnel-patient-list';
$base_path = '../..';
$activePage = 'patient_list';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/personnel_header.php';

// Fetch non-archived patients
$sql = "SELECT PatientID, PatientName, RoomNumber FROM patients WHERE IsArchived = FALSE ORDER BY PatientName";
$patients = $conn->query($sql);
$today = date('Y-m-d');
?>

<main>
  <h1 class="page-title">Patient Management</h1>

  <div class="list-container">
    <div id="global-message-panel" style="display: none; margin-bottom: 20px; padding: 1rem; border-radius: 8px;"></div>

    <!-- Top Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <input id="search-input" type="text" placeholder="Search ID, Name or Room..." class="search-bar-input" />
      <button id="toggleAddForm" class="btn btn-add">+ Add Patient</button>
    </div>

    <!-- Add Patient Form -->
    <div id="addPatientContainer" class="add-med-form hidden" style="margin-bottom: 20px;">
        <h3 class="form-title">Add New Patient</h3>
        <div id="add-patient-message" style="display: none; margin-bottom: 1rem; padding: 1rem; border-radius: 8px; color: white;"></div>
        <form id="addPatientForm" action="add_patient.php" method="POST">
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
                    <input type="date" id="Birthdate" name="Birthdate" required max="<?= $today ?>">
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
                  <a href="<?= $base_path ?>/public/personnel/patient_med_history.php?id=<?= urlencode($p['PatientID']) ?>" class="btn btn-view">View</a>
                  <a href="<?= $base_path ?>/public/personnel/patient_edit.php?id=<?= urlencode($p['PatientID']) ?>" class="btn btn-edit">Edit</a>
                  <button class="btn btn-archive" onclick="archivePatient('<?= htmlspecialchars($p['PatientID']) ?>')">
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
  document.addEventListener('DOMContentLoaded', function() {
    const globalMessagePanel = document.getElementById('global-message-panel');

    function showGlobalMessage(message, type = 'success') {
        globalMessagePanel.textContent = message;
        globalMessagePanel.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
        globalMessagePanel.style.color = 'white';
        globalMessagePanel.style.display = 'block';

        setTimeout(() => {
            globalMessagePanel.style.transition = 'opacity 0.5s ease';
            globalMessagePanel.style.opacity = '0';
            setTimeout(() => {
                globalMessagePanel.style.display = 'none';
                globalMessagePanel.style.opacity = '1'; // Reset for next time
            }, 500);
        }, 4000);
    }

    // Toggle Add Patient Form
    const toggleBtn = document.getElementById('toggleAddForm');
    const formContainer = document.getElementById('addPatientContainer');
    const cancelBtn = document.getElementById('cancelAddPatient');
    const patientIdInput = document.getElementById('PatientID');
    const addPatientForm = document.getElementById('addPatientForm');
    const formMessageDiv = document.getElementById('add-patient-message');
    const savePatientBtn = formContainer.querySelector('button[type="submit"]');

    // Set max date for birthdate input
    const birthdateInput = document.getElementById('Birthdate');
    if (birthdateInput) {
        birthdateInput.max = new Date().toISOString().split("T")[0];
    }

    let idCheckTimeout;

    if (toggleBtn && formContainer && cancelBtn) {
      toggleBtn.addEventListener('click', function () {
        formContainer.classList.remove('hidden');
        toggleBtn.disabled = true;
      });

      cancelBtn.addEventListener('click', function () {
        formContainer.classList.add('hidden');
        toggleBtn.disabled = false;
        addPatientForm.reset();
        patientIdInput.classList.remove('valid', 'invalid');
        savePatientBtn.disabled = false;
        formMessageDiv.style.display = 'none';
      });
    }

    // AJAX Form Submission
    if (addPatientForm) {
        addPatientForm.addEventListener('submit', function(event) {
            event.preventDefault();
            savePatientBtn.disabled = true;
            savePatientBtn.textContent = 'Saving...';
            formMessageDiv.style.display = 'none';

            const formData = new FormData(addPatientForm);

            fetch('add_patient.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showGlobalMessage(data.message, 'success');
                    addPatientRowToTable(data.patient);
                    cancelBtn.click(); // close and reset the form
                } else {
                    formMessageDiv.textContent = data.message;
                    formMessageDiv.style.backgroundColor = '#dc3545';
                    formMessageDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Submission error:', error);
                formMessageDiv.textContent = 'An unexpected network error occurred. Please try again.';
                formMessageDiv.style.backgroundColor = '#dc3545';
                formMessageDiv.style.display = 'block';
            })
            .finally(() => {
                savePatientBtn.disabled = false;
                savePatientBtn.textContent = 'Save Patient';
            });
        });
    }

    // Live Search for table
    const searchInput = document.getElementById('search-input');
    const table = document.getElementById('patient-table');
    searchInput.addEventListener('input', () => {
      const filter = searchInput.value.toLowerCase();
      for (let row of table.tBodies[0].rows) {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      }
    });

    // Real-time Patient ID validation
    patientIdInput.addEventListener('input', () => {
        clearTimeout(idCheckTimeout);
        const patientId = patientIdInput.value.trim();

        if (patientId === '') {
            patientIdInput.classList.remove('valid', 'invalid');
            savePatientBtn.disabled = false;
            return;
        }

        idCheckTimeout = setTimeout(() => {
            fetch(`check_patient_id.php?id=${encodeURIComponent(patientId)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        patientIdInput.classList.add('invalid');
                        patientIdInput.classList.remove('valid');
                        savePatientBtn.disabled = true;
                    } else {
                        patientIdInput.classList.add('valid');
                        patientIdInput.classList.remove('invalid');
                        savePatientBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error validating Patient ID:', error);
                    savePatientBtn.disabled = false;
                });
        }, 500); // Debounce for 500ms
    });
  });

  function addPatientRowToTable(patient) {
      const tableBody = document.querySelector('#patient-table tbody');
      const noPatientsRow = tableBody.querySelector('td[colspan="4"]');
      if (noPatientsRow) {
          noPatientsRow.parentElement.remove();
      }

      const newRow = document.createElement('tr');
      const basePath = '<?= $base_path ?>';
      
      // Function to prevent XSS
      const escapeHTML = str => {
          if (str === null || str === undefined) return '';
          const p = document.createElement('p');
          p.appendChild(document.createTextNode(str));
          return p.innerHTML;
      }

      newRow.innerHTML = `
          <td>${escapeHTML(patient.PatientID)}</td>
          <td>${escapeHTML(patient.PatientName)}</td>
          <td>${escapeHTML(patient.RoomNumber)}</td>
          <td class="action-cell">
              <a href="${basePath}/public/personnel/patient_med_history.php?id=${encodeURIComponent(patient.PatientID)}" class="btn btn-view">View</a>
              <a href="${basePath}/public/personnel/patient_edit.php?id=${encodeURIComponent(patient.PatientID)}" class="btn btn-edit">Edit</a>
              <button class="btn btn-archive" onclick="archivePatient('${escapeHTML(patient.PatientID)}')">
                  <i class="fas fa-archive"></i>
              </button>
          </td>
      `;
      // Prepend to make the new patient appear at the top
      tableBody.prepend(newRow);
  }

  // Archive Patient
  function archivePatient(pid) {
    if (confirm(`Archive patient: ${pid}? This will deactivate their profile.`)) {
      fetch(`archive_patient.php?id=${encodeURIComponent(pid)}`)
        .then(res => {
          if (res.ok) {
            // Find the row and remove it from the table
            const row = document.querySelector(`button[onclick="archivePatient('${pid}')"]`).closest('tr');
            if(row) {
                row.remove();
            }
            showGlobalMessage(`Patient ${pid} archived successfully.`, 'success');
          } else {
            alert('Archive failed.');
          }
        })
        .catch(() => alert('Request error.'));
    }
  }
</script>

<style>
    .form-group input.valid {
        border-color: #28a745;
        box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
    }
    .form-group input.invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
    }
</style>

<?php
require_once __DIR__ . '/../../templates/partials/personnel_side_menu.php';
require_once __DIR__ . '/../../templates/partials/personnel_footer.php';
?>
