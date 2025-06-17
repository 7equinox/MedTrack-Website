document.addEventListener('DOMContentLoaded', function() {
    // --- Common Personnel Area functionality ---

    // 1. Side Menu Toggle
    const menuToggle = document.getElementById('menuToggle');
    const sideMenu = document.getElementById('sideMenu');
    const closeMenu = document.getElementById('closeMenu');
    const menuOverlay = document.getElementById('menuOverlay');

    const openMenu = () => {
        if (sideMenu) sideMenu.classList.add('open');
        if (menuOverlay) menuOverlay.classList.add('show');
    };

    const closeAll = () => {
        if (sideMenu) sideMenu.classList.remove('open');
        if (menuOverlay) menuOverlay.classList.remove('show');
    };

    if (menuToggle) menuToggle.addEventListener('click', openMenu);
    if (closeMenu) closeMenu.addEventListener('click', closeAll);
    if (menuOverlay) menuOverlay.addEventListener('click', closeAll);

    // 2. Active Side Menu Link Highlighting
    const currentPagePath = window.location.pathname;
    const sideMenuLinks = document.querySelectorAll('.side-menu-links a, .side-menu-content .profile-link');

    sideMenuLinks.forEach(link => {
        try {
            const linkPath = new URL(link.href).pathname;
            if (linkPath === currentPagePath) {
                link.classList.add('active');
                // If it's the profile link, also activate the container
                if (link.classList.contains('profile-link')) {
                    link.querySelector('.profile-header').classList.add('active');
                }
            }
        } catch (e) {
            console.error(`Could not parse URL for link: ${link.href}`, e);
        }
    });

    // --- Page-specific functionality ---

    // 1. Personnel Dashboard: Add Medication and Searchable Selects
    const addMedBtn = document.getElementById('add-med-btn');
    if (addMedBtn) {
        const patientListBody = document.getElementById('patient-list-body');
        const newMedTemplate = document.getElementById('new-med-template');
        
        // Dummy Data
        const patientData = [
            { id: '101', name: 'John Smith', room: '201A' },
            { id: '102', name: 'Jane Doe', room: '202B' },
            { id: '103', name: 'Peter Jones', room: '203A' },
            { id: '104', name: 'Mary Johnson', room: '204C' },
            { id: '105', name: 'David Williams', room: '205A' },
            { id: '106', name: 'Emily Brown', room: '206B' },
            { id: '107', name: 'Michael Davis', room: '301A' },
        ];

        addMedBtn.addEventListener('click', () => {
            if (patientListBody.querySelector('.new-med-row')) return;

            const newRow = newMedTemplate.content.cloneNode(true);
            patientListBody.prepend(newRow);
            const addedRow = patientListBody.querySelector('.new-med-row');
            addMedBtn.style.display = 'none';

            initializeSearchableSelects(addedRow, patientData);

            addedRow.querySelector('.cancel-icon').addEventListener('click', () => {
                addedRow.remove();
                addMedBtn.style.display = 'block';
            });

            addedRow.querySelector('.save-icon').addEventListener('click', () => {
                const inputs = addedRow.querySelectorAll('input');
                const patientId = inputs[0].value;
                const patientName = inputs[1].value;
                const medication = inputs[2].value;
                const dosage = inputs[3].value;
                const time = inputs[4].value;
                const room = inputs[5].value;

                if (!patientId || !medication || !time) {
                    alert('Patient, Medication, and Time are required.');
                    return;
                }

                const newStaticRow = document.createElement('tr');
                newStaticRow.innerHTML = `
                    <td>${patientId}</td>
                    <td>${patientName}</td>
                    <td>${medication}</td>
                    <td>${dosage}</td>
                    <td>${time}</td>
                    <td>${room}</td>
                `;
                addedRow.replaceWith(newStaticRow);
                addMedBtn.style.display = 'block';
            });
        });

        function initializeSearchableSelects(row, data) {
            const selects = row.querySelectorAll('.searchable-select');
            selects.forEach(select => {
                const input = select.querySelector('input');
                const optionsList = select.querySelector('.options-list');
                const type = select.dataset.type;

                optionsList.innerHTML = data.map(item => 
                    `<div class="option" data-id="${item.id}" data-name="${item.name}" data-room="${item.room}">${item[type]}</div>`
                ).join('');

                input.addEventListener('input', () => {
                    const filter = input.value.toLowerCase();
                    optionsList.querySelectorAll('.option').forEach(option => {
                        option.classList.toggle('hidden', !option.textContent.toLowerCase().includes(filter));
                    });
                });

                input.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeAllSelects();
                    select.classList.add('active');
                });

                optionsList.addEventListener('click', (e) => {
                    if (e.target.classList.contains('option')) {
                        const { id, name, room } = e.target.dataset;
                        row.querySelector('[data-type="id"] input').value = id;
                        row.querySelector('[data-type="name"] input').value = name;
                        row.querySelector('input[placeholder="Room"]').value = room;
                        closeAllSelects();
                    }
                });
            });
        }

        function closeAllSelects() {
            document.querySelectorAll('.searchable-select.active').forEach(s => s.classList.remove('active'));
        }

        document.addEventListener('click', closeAllSelects);
    }

    // 2. Patient Med Sched Edit: Inline editing for schedule list
    const scheduleList = document.querySelector('.schedule-list');
    if (scheduleList) {
        let currentlyEditing = null;

        scheduleList.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.edit-btn');
            const saveBtn = e.target.closest('.save-btn');
            const selectTrigger = e.target.closest('.custom-select__trigger');
            const selectOption = e.target.closest('.custom-option');

            if (editBtn) {
                e.preventDefault();
                const card = editBtn.closest('.schedule-card');
                if (currentlyEditing && currentlyEditing !== card) {
                    revertCard(currentlyEditing);
                }
                enterEditMode(card);
                currentlyEditing = card;
            } else if (saveBtn) {
                e.preventDefault();
                const card = saveBtn.closest('.schedule-card');
                saveChanges(card);
                currentlyEditing = null;
            } else if (selectTrigger) {
                selectTrigger.closest('.custom-select').classList.toggle('open');
            } else if (selectOption) {
                const select = selectOption.closest('.custom-select');
                const triggerSpan = select.querySelector('.custom-select__trigger span');
                triggerSpan.textContent = selectOption.textContent.trim();
                select.classList.remove('open');
            }
        });

        document.addEventListener('click', (e) => {
            const openSelect = document.querySelector('.custom-select.open');
            if (openSelect && !openSelect.contains(e.target)) {
                openSelect.classList.remove('open');
            }
        });

        function enterEditMode(card) {
            card.classList.add('editing');
            const displayView = card.querySelector('.schedule-display');
            const h2 = displayView.querySelector('h2');
            const p = displayView.querySelector('p');

            const [medName, intakeTime] = h2.textContent.split(' | ');
            const [dosage, status] = p.textContent.split(' | ');
            
            const form = card.querySelector('.edit-form');
            form.querySelector('input[id^="medication"]').value = medName.trim();
            form.querySelector('input[type="time"]').value = convertTo24Hour(intakeTime.trim());
            form.querySelector('input[id^="dosage"]').value = dosage.trim();

            const statusTrigger = form.querySelector('.custom-select__trigger span');
            statusTrigger.textContent = status.trim();
            const radioOptions = form.querySelectorAll('.custom-option input[type="radio"]');
            radioOptions.forEach(opt => {
                if (opt.value.toLowerCase() === status.trim().toLowerCase()) {
                    opt.checked = true;
                }
            });
        }

        function saveChanges(card) {
            const form = card.querySelector('.edit-form');
            const medName = form.querySelector('input[id^="medication"]').value;
            const intakeTime = form.querySelector('input[type="time"]').value;
            const dosage = form.querySelector('input[id^="dosage"]').value;
            const statusRadio = form.querySelector('.custom-option input:checked');
            const status = statusRadio ? statusRadio.value : 'Upcoming';
            
            const displayView = card.querySelector('.schedule-display');
            displayView.querySelector('h2').textContent = `${medName} | ${convertTo12Hour(intakeTime)}`;
            displayView.querySelector('p').textContent = `${dosage} | ${status}`;

            revertCard(card);
        }
        
        function revertCard(card) {
            card.classList.remove('editing');
            const openSelect = card.querySelector('.custom-select.open');
            if (openSelect) {
                openSelect.classList.remove('open');
            }
        }

        function convertTo12Hour(time) {
            if (!time) return '';
            let [hours, minutes] = time.split(':');
            let ampm = parseInt(hours, 10) >= 12 ? 'PM' : 'AM';
            hours = parseInt(hours, 10) % 12;
            hours = hours ? hours : 12; 
            return `${hours}:${minutes} ${ampm}`;
        }

        function convertTo24Hour(time) {
            if (!time) return '';
            const [timePart, ampm] = time.split(' ');
            let [hours, minutes] = timePart.split(':');
            hours = parseInt(hours, 10);
            if (ampm && ampm.toUpperCase() === 'PM' && hours !== 12) {
                hours += 12;
            }
            if (ampm && ampm.toUpperCase() === 'AM' && hours === 12) {
                hours = 0;
            }
            return `${String(hours).padStart(2, '0')}:${minutes}`;
        }
    }

    // 3. Personnel Profile Page Logic
    if (document.querySelector('.profile-form-card')) {
        const DUMMY_DATA = {
            "MD-2023": {
                birthdate: "01/10/1988",
                age: "36",
                name: "Dr. Sarah Johnson",
                sex: "Female",
                address: "123 Health St, Wellness City",
                email: "s.johnson@medtrack.com",
                contact: "(123) 456-7890",
                pic: "https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
            },
            "PT-001": {
                birthdate: "05/15/1990",
                age: "34",
                name: "John Doe",
                sex: "Male",
                address: "456 Patient Ave, Sickville",
                email: "j.doe@email.com",
                contact: "(987) 654-3210",
                pic: "https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
            },
            "PT-002": {
                birthdate: "11/20/1985",
                age: "38",
                name: "Jane Smith",
                sex: "Female",
                address: "789 Cure Blvd, Healtown",
                email: "j.smith@email.com",
                contact: "(555) 123-4567",
                pic: "https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
            },
             "PT-003": {
                birthdate: "03/03/2003",
                age: "21",
                name: "Emily White",
                sex: "Female",
                address: "101 Recovery Road, Wellburg",
                email: "e.white@email.com",
                contact: "(555) 987-6543",
                pic: "https://images.pexels.com/photos/1181519/pexels-photo-1181519.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
            }
        };

        function convertToYYYYMMDD(dateString) { // MM/DD/YYYY to YYYY-MM-DD
            if (!dateString || dateString.includes('-')) return dateString;
            const parts = dateString.split('/');
            if (parts.length === 3) {
                return `${parts[2]}-${parts[0].padStart(2, '0')}-${parts[1].padStart(2, '0')}`;
            }
            return dateString;
        }

        function convertToMMDDYYYY(dateString) { // YYYY-MM-DD to MM/DD/YYYY
            if (!dateString || dateString.includes('/')) return dateString;
            const parts = dateString.split('-');
            if (parts.length === 3) {
                return `${parts[1]}/${parts[2]}/${parts[0]}`;
            }
            return dateString;
        }

        function populateForm(id) {
            const data = DUMMY_DATA[id];
            if (!data) return;

            document.querySelectorAll('.profile-form-card input[type="text"], .profile-form-card input[type="email"]').forEach(input => {
                input.readOnly = true;
                if (input.id !== 'birthdate') {
                     input.placeholder = '';
                }
            });

            document.getElementById('birthdate').value = data.birthdate || '';
            document.getElementById('age').value = data.age || '';
            document.getElementById('name').value = data.name || '';
            document.getElementById('address').value = data.address || '';
            document.getElementById('email').value = data.email || '';
            document.getElementById('contact').value = data.contact || '';
            document.querySelector('.profile-sidebar .profile-pic').src = data.pic;
            
            // Set Sex Dropdown
            const sexSelect = document.getElementById('sex-select');
            const sexTriggerSpan = sexSelect.querySelector('.custom-select-trigger span');
            const sexOption = sexSelect.querySelector(`input[name="sex"][value="${data.sex}"]`);
            if (sexOption) {
                sexOption.checked = true;
                sexTriggerSpan.textContent = data.sex;
            } else {
                 sexTriggerSpan.textContent = "Select";
                 const checkedSex = document.querySelector('input[name="sex"]:checked');
                 if(checkedSex) checkedSex.checked = false;
            }
        }

        function clearAndEnableForm() {
            const profileForm = document.querySelector('.profile-form-card');
            
            profileForm.querySelectorAll('input').forEach(input => {
                input.value = '';
                input.readOnly = false;
            });

            const birthdateInput = document.getElementById('birthdate');
            birthdateInput.placeholder = 'MM/DD/YYYY';
            birthdateInput.readOnly = true; // Keep it readonly until icon is clicked
            
            document.getElementById('age').readOnly = true; // Keep it readonly until icon is clicked

            document.getElementById('personnel-id-select').querySelector('.custom-select-trigger span').textContent = "New Patient";
            document.getElementById('personnel-id-select').querySelector('.custom-select-trigger').dataset.value = "new-patient";
            document.querySelector('.profile-sidebar .profile-pic').src = "https://via.placeholder.com/150";

            // Reset sex dropdown
            const sexSelect = document.getElementById('sex-select');
            const sexTriggerSpan = sexSelect.querySelector('.custom-select-trigger span');
            sexTriggerSpan.textContent = "Select";
            const checkedSex = document.querySelector('input[name="sex"]:checked');
            if(checkedSex) checkedSex.checked = false;
            
            profileForm.querySelector('#name').focus();
        }

        // --- Age Calculation ---
        const birthdateField = document.getElementById('birthdate');
        if (birthdateField) {
            birthdateField.addEventListener('change', () => {
                if (birthdateField.type === 'date' && birthdateField.value) {
                    const birthDate = new Date(birthdateField.value);
                    const today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const m = today.getMonth() - birthDate.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    document.getElementById('age').value = age;
                }
            });
        }

        // --- Field Editing Logic ---
        document.querySelectorAll('.edit-icon').forEach(icon => {
            icon.addEventListener('click', (e) => {
                const currentIcon = e.currentTarget;
                const targetInputId = currentIcon.dataset.target;
                const targetInput = document.getElementById(targetInputId);

                if (targetInput && targetInput.readOnly) {
                    currentIcon.style.display = 'none';
                    targetInput.readOnly = false;
                    
                    targetInput.focus();

                    const onBlur = () => {
                        targetInput.readOnly = true;
                        currentIcon.style.display = '';
                        targetInput.removeEventListener('keydown', onKeydown);
                    };

                    const onKeydown = (event) => {
                        if (event.key === 'Enter') {
                            targetInput.blur();
                        }
                    };

                    targetInput.addEventListener('blur', onBlur, { once: true });
                    targetInput.addEventListener('keydown', onKeydown);
                }
            });
        });

        // --- Custom Dropdown Logic (Generic) ---
        function initializeDropdown(dropdownId) {
            const select = document.getElementById(dropdownId);
            if (!select) return;

            const trigger = select.querySelector('.custom-select-trigger');
            
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                closeAllDropdowns();
                select.classList.toggle('open');
            });

            select.querySelectorAll('.custom-option').forEach(option => {
                option.addEventListener('click', () => {
                    const selectedValue = option.dataset.value;
                    const selectedText = option.textContent.trim();
                    
                    trigger.querySelector('span').textContent = selectedText;
                    trigger.dataset.value = selectedValue;
                    select.classList.remove('open');

                    if (dropdownId === 'personnel-id-select') {
                        if (selectedValue === 'add-new') {
                            clearAndEnableForm();
                        } else {
                            populateForm(selectedValue);
                        }
                    } else if (dropdownId === 'sex-select') {
                         const radio = option.querySelector('input[type="radio"]');
                         if(radio) radio.checked = true;
                    }
                });
            });
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.custom-select-wrapper.open').forEach(openSelect => {
                openSelect.classList.remove('open');
            });
        }

        initializeDropdown('personnel-id-select');
        initializeDropdown('sex-select');

        // --- Close dropdown when clicking outside ---
        document.addEventListener('click', (e) => {
            closeAllDropdowns();
        });

        // --- Save Button Logic ---
        document.querySelector('.save-btn').addEventListener('click', () => {
            const personnelIdTrigger = document.getElementById('personnel-id-select').querySelector('.custom-select-trigger');
            const sexSelect = document.getElementById('sex-select');

            const birthdateInput = document.getElementById('birthdate');
            let birthdateValue = birthdateInput.value;
            if (birthdateInput.type === 'date') {
                birthdateValue = convertToMMDDYYYY(birthdateInput.value);
            }

            const data = {
                personnelId: personnelIdTrigger.dataset.value,
                birthdate: birthdateValue,
                age: document.getElementById('age').value,
                name: document.getElementById('name').value,
                sex: sexSelect.querySelector('input[name="sex"]:checked')?.value || '',
                address: document.getElementById('address').value,
                email: document.getElementById('email').value,
                contact: document.getElementById('contact').value,
            };
            console.log('Saved Data:', data);

            // Revert input types and make readonly
            if (birthdateInput.type === 'date') {
                birthdateInput.type = 'text';
                birthdateInput.value = birthdateValue;
            }
            const ageInput = document.getElementById('age');
            if (ageInput.type === 'number') {
                ageInput.type = 'text';
            }

            document.querySelectorAll('.profile-form-card input').forEach(input => {
                input.readOnly = true;
            });

            // Show all edit icons again
            document.querySelectorAll('.edit-icon').forEach(icon => {
                icon.style.display = '';
            });

            alert('Profile Saved!');
        });

        // --- Profile Picture Change ---
        const profilePic = document.querySelector('.profile-sidebar .profile-pic');
        const uploader = document.getElementById('profile-pic-upload');
        if (profilePic && uploader) {
            uploader.addEventListener('change', (e) => {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        profilePic.src = event.target.result;
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
    }

    // 4. Access Report Log Page Logic
    const statusDropdown = document.getElementById('statusDropdown');
    if (statusDropdown) {
        const trigger = statusDropdown.querySelector('.status-dropdown-trigger');
        const options = statusDropdown.querySelectorAll('.status-option');

        // --- Toggle dropdown ---
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            statusDropdown.classList.toggle('open');
        });

        // --- Handle option selection ---
        options.forEach(option => {
            option.addEventListener('click', () => {
                const radio = option.querySelector('input');
                if (!radio) return;

                const selectedValue = radio.value;
                const selectedText = option.textContent.trim();
                const triggerSpan = trigger.querySelector('span');

                // 1. Update trigger text and color
                triggerSpan.textContent = selectedText;
                trigger.className = 'status-dropdown-trigger'; // Reset classes
                trigger.classList.add(`status-${selectedValue}`);
                
                // 2. Uncheck others and check this one
                options.forEach(opt => opt.querySelector('input').checked = false);
                radio.checked = true;

                // 3. Close dropdown
                statusDropdown.classList.remove('open');
            });
        });

        // --- Close dropdown when clicking outside ---
        document.addEventListener('click', (e) => {
            if (!statusDropdown.contains(e.target) && statusDropdown.classList.contains('open')) {
                statusDropdown.classList.remove('open');
            }
        });
    }

    // 6. Add Medication Form Toggle
    const toggleButton = document.getElementById('toggle-med-form');
    const medForm = document.getElementById('medication-form');
    const cancelButton = document.getElementById('cancel-med-form');

    if (toggleButton && medForm && cancelButton) {
        toggleButton.addEventListener('click', function() {
            medForm.classList.remove('hidden');
            this.classList.add('hidden');
        });

        cancelButton.addEventListener('click', function() {
            medForm.classList.add('hidden');
            toggleButton.classList.remove('hidden');
        });
    }

    // 7. Live Search for Personnel Dashboard
    const searchInput = document.getElementById('dashboard-search-input');
    const tableBody = document.getElementById('patient-list-body');
    const paginationContainer = document.getElementById('pagination-container');
    const patientListContainer = document.querySelector('.patient-list-container');
    let debounceTimer;

    const performSearch = (searchTerm, page = 1) => {
        const url = `live_search.php?search=${encodeURIComponent(searchTerm)}&page=${page}`;
        
        if(tableBody) tableBody.style.opacity = '0.5';

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (tableBody) {
                    tableBody.innerHTML = data.tableBody;
                    tableBody.style.opacity = '1';
                }
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination;
                }
            })
            .catch(error => {
                console.error('Error fetching search results:', error);
                if(tableBody) {
                    tableBody.innerHTML = '<tr><td colspan="10">Error loading data. Please try again.</td></tr>';
                    tableBody.style.opacity = '1';
                }
            });
    };

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(e.target.value, 1);
            }, 500);
        });
    }

    if (patientListContainer) {
        patientListContainer.addEventListener('click', (e) => {
            if (e.target.matches('.pagination-container a.btn-page')) {
                e.preventDefault();
                const link = e.target;
                if (!link.classList.contains('disabled') && !link.classList.contains('active')) {
                    const page = link.dataset.page;
                    const searchTerm = searchInput ? searchInput.value : '';
                    performSearch(searchTerm, page);
                }
            }
        });
    }
}); 