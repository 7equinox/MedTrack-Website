# MedTrack - Medication Tracking System

MedTrack is a web-based application designed to help manage medication schedules for patients. It provides distinct interfaces for Patients, Doctors, and Administrators to ensure seamless tracking and management of medical treatments.

## Key Features

*   **Three User Roles**: Separate login and dashboard areas for Admins, Doctors, and Patients.
*   **Automated Setup**: The application automatically sets up the database schema and seeds it with sample data on first run.
*   **Admin Dashboard**: Manage doctor and patient accounts, including adding, editing, and archiving.
*   **Doctor Dashboard**: View assigned patients, manage medication schedules, and track patient adherence.
*   **Patient Dashboard**: View personal medication schedules and history.
*   **Secure Password Handling**: User passwords for admins and doctors are securely stored using modern hashing techniques.

## Setup and Installation

1.  **Prerequisites**: Make sure you have a local web server environment (like XAMPP, WAMP, or MAMP) with PHP and MySQL.
2.  **Database Connection**:
    *   The application will attempt to automatically create the `medtrackdb` database.
    *   The default database credentials are set in `config/database.php` for a standard local setup (`root` with no password). If your environment differs, please update this file with your MySQL host, username, and password.
3.  **Run the Application**: Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP) and navigate to the `public` folder in your browser. The application should load, and the database will be set up automatically if it doesn't already exist.

## Logging In

The application has three separate login portals.

*   **Admin**: Navigates to `admin_login.php`.
*   **Doctor**: Navigates to `doctor_login.php`.
*   **Patient**: Navigates to `patient_login.php`.

You can find sample `AdminID`, `DoctorID`, and `PatientID` values by inspecting the `admins`, `doctor`, and `patients` tables in your `medtrackdb` database using a tool like phpMyAdmin.

### Password Management (Admins & Doctors)

Admin and Doctor passwords are not stored in plain text. We utilize a secure **hashing algorithm** to protect them. As a result, you cannot simply look up a password in the database.

If you need to log into an admin or doctor account from the sample data, you must "reset" the password using the **Forgot Password** functionality.

**How to Reset a Password:**

1.  Go to the appropriate login page (Admin or Doctor).
2.  Click the **"Forgot Password"** link.
3.  You will be asked to enter the `AdminID` or `DoctorID`. You can find a valid ID from the database (e.g., for admins, `AD-001`; for doctors, `DT-0001`).
4.  **Simulated Email/Code:** For demonstration purposes, the application does not actually send an email. It will show a confirmation message pretending a recovery link or code has been sent. You can simply proceed to the next step. On the recovery code page, **you may enter any code** to continue.
5.  You will then be directed to a page to set a new password. Enter your new password, and you'll be able to log in.

### Patient Login

Patients log in using only their `PatientID`. There is no password required for patient accounts in the current version. You can find a list of valid patient IDs in the `patients` table (e.g., `PT-0002`). 