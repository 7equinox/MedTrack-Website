<?php

function setupDatabase($conn) {
    // Create database if it doesn't exist
    $dbName = 'medtrackdb';
    $checkDbQuery = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'";
    $result = $conn->query($checkDbQuery);
    if ($result->num_rows == 0) {
        $createDbQuery = "CREATE DATABASE $dbName";
        if (!$conn->query($createDbQuery)) {
            die("Error creating database: " . $conn->error);
        }
    }
    
    $conn->select_db($dbName);

    // Check if tables exist. If so, assume DB is set up.
    $checkTables = "SHOW TABLES LIKE 'patients'";
    $tablesResult = $conn->query($checkTables);
    if($tablesResult->num_rows > 0) {
        return; // Tables exist, setup not needed.
    }

    $sql_commands = [
        "CREATE TABLE `admins` (
          `AdminID` varchar(255) NOT NULL,
          `AdminName` varchar(255) NOT NULL,
          `Email` varchar(255) NOT NULL,
          `Password` varchar(255) NOT NULL,
          `reset_token` varchar(255) DEFAULT NULL,
          `reset_token_expires` datetime DEFAULT NULL,
          PRIMARY KEY (`AdminID`),
          UNIQUE KEY `Email` (`Email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        'INSERT INTO `admins` (`AdminID`, `AdminName`, `Email`, `Password`) VALUES
        (\'AD-001\', \'Admin Uno\', \'admin1@medtrack.ph\', \'$2y$10$oVClfH23JYaBo6SJpQp7cehDAEMFcvRKiRTe7eaO78q6RwpUImWjK\'),
        (\'AD-002\', \'Admin Dos\', \'admin2@medtrack.ph\', \'$2y$10$DnheDgEAJNR.TxuC9AdYCuthD6cVPiQs1ZKAT4q3Q.5IYUl9n6Hdq\'),
        (\'AD-003\', \'Admin Tres\', \'admin3@medtrack.ph\', \'$2y$10$4D0QfnRL725iQiLzt9CppuZUUNhtiQvdmK78EIi9g5f6siTZuyEPm\');',

        "CREATE TABLE `doctor` (
          `DoctorID` varchar(255) NOT NULL,
          `DoctorName` varchar(255) DEFAULT NULL,
          `Password` varchar(255) NOT NULL,
          `Email` varchar(255) NOT NULL,
          `ContactNumber` varchar(255) DEFAULT NULL,
          `ProfilePicture` varchar(255) DEFAULT NULL,
          `IsArchived` tinyint(1) NOT NULL DEFAULT 0,
          PRIMARY KEY (`DoctorID`),
          UNIQUE KEY `Email` (`Email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        'INSERT INTO `doctor` (`DoctorID`, `DoctorName`, `Password`, `Email`, `ContactNumber`, `ProfilePicture`, `IsArchived`) VALUES
        (\'DT-0001\', \'Dr. Angelo Santos\', \'$2y$10$HUoj1xXFLN4Mfcpo9/2fVOlwSWnsglyVskH7qBxPFGIrGr5MwcH9m\', \'a.santos@medtrack.ph\', \'09171110001\', NULL, 0),
        (\'DT-0002\', \'Dr. Sofia Reyes\', \'$2y$10$NrILV.W.avgqZviZ3XO4QuFsj2soG9iwd0avRAZL6ezBKCCX00MEa\', \'s.reyes@medtrack.ph\', \'09171110002\', NULL, 0),
        (\'DT-0003\', \'Dr. Miguel Castro\', \'$2y$10$QSASCLpmGRbn0H8Mr6HY7OMDoW5l6tMfzldMLv4iDWxkDBb.vGkc2\', \'m.castro@medtrack.ph\', \'09171110003\', NULL, 0),
        (\'DT-0004\', \'Dr. Isabella Lim\', \'$2y$10$6Q6PLJbHb0cHYc5/9MQ8veEGvIWvwpExWm5oWAbBCsIy3cgF.XPWu\', \'i.lim@medtrack.ph\', \'09171110004\', NULL, 0),
        (\'DT-0005\', \'Dr. Gabriel Tan\', \'$2y$10$z/qofRWhS/6HIo5oLVHYf.AhwiaVi3/ztOw2C56nkFYxSuoHzuCma\', \'g.tan@medtrack.ph\', \'09171110005\', NULL, 0);',

        "CREATE TABLE `patients` (
          `PatientID` varchar(255) NOT NULL,
          `PatientName` varchar(255) NOT NULL,
          `Birthdate` date DEFAULT NULL,
          `Sex` enum('Male','Female','Other') DEFAULT NULL,
          `HomeAddress` text DEFAULT NULL,
          `Email` varchar(255) DEFAULT NULL,
          `ContactNumber` varchar(255) DEFAULT NULL,
          `RoomNumber` varchar(255) DEFAULT NULL,
          `ProfilePicture` varchar(255) DEFAULT NULL,
          `IsArchived` tinyint(1) DEFAULT 0,
          PRIMARY KEY (`PatientID`),
          UNIQUE KEY `Email` (`Email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        "INSERT INTO `patients` (`PatientID`, `PatientName`, `Birthdate`, `Sex`, `HomeAddress`, `Email`, `ContactNumber`, `RoomNumber`, `ProfilePicture`, `IsArchived`) VALUES
        ('PT-0001', 'Althea Villanueva', '1988-05-14', 'Female', '15 Sampaguita St, Quezon City', 'althea.v@email.com', '09282220001', '101', NULL, 1),
        ('PT-0002', 'Joaquin Gonzales', '1995-02-20', 'Male', '23 Narra Ave, Makati City', 'joaquin.g@email.com', '09282220002', '102', NULL, 0),
        ('PT-0003', 'Samantha Torres', '1976-11-30', 'Female', '45 Mango Drive, Cebu City', 'samantha.t@email.com', '09282220003', '103', NULL, 0),
        ('PT-0004', 'Elijah Ramos', '2002-09-01', 'Male', '110 Acacia Lane, Davao City', 'elijah.r@email.com', '09282220004', '104', NULL, 0),
        ('PT-0005', 'Chloe Garcia', '1968-01-25', 'Female', '21 Orchid St, Baguio City', 'chloe.g@email.com', '09282220005', '201', NULL, 0),
        ('PT-0006', 'Liam Mendoza', '1989-07-11', 'Male', '33 Rosewood St, Iloilo City', 'liam.m@email.com', '09282220006', '202', NULL, 0),
        ('PT-0007', 'Sophia Castillo', '2000-04-16', 'Female', '88 Pearl Drive, Pasig City', 'sophia.c@email.com', '09282220007', '203', NULL, 0),
        ('PT-0008', 'Lucas Ocampo', '1959-12-08', 'Male', '77 Diamond St, Mandaluyong City', 'lucas.o@email.com', '09282220008', '204', NULL, 0),
        ('PT-0009', 'Isla Bautista', '1979-08-03', 'Female', '99 Gold St, San Juan City', 'isla.b@email.com', '09282220009', '301', NULL, 0),
        ('PT-0010', 'Mateo Salazar', '1998-03-22', 'Male', '12 Silver St, Taguig City', 'mateo.s@email.com', '09282220010', '302', NULL, 0),
        ('PT-0011', 'Amelia Diaz', '1985-06-18', 'Female', '28 Bronze St, Marikina City', 'amelia.d@email.com', '09282220011', '303', NULL, 1),
        ('PT-0012', 'Leo de Leon', '2008-10-15', 'Male', '34 Steel St, Valenzuela City', 'leo.d@email.com', '09282220012', '304', NULL, 0),
        ('PT-0013', 'Aurora Ignacio', '1952-07-28', 'Female', '56 Copper St, Parañaque City', 'aurora.i@email.com', '09282220013', '401', NULL, 0),
        ('PT-0014', 'Javier Mercado', '1993-01-09', 'Male', '72 Nickel St, Las Piñas City', 'javier.m@email.com', '09282220014', '402', NULL, 0),
        ('PT-0015', 'Natalia Corpuz', '1971-11-04', 'Female', '81 Platinum St, Muntinlupa City', 'natalia.c@email.com', '09282220015', '403', NULL, 0);",

        "CREATE TABLE `medicationschedule` (
          `ScheduleID` int NOT NULL AUTO_INCREMENT,
          `PatientID` varchar(255) DEFAULT NULL,
          `PrescriptionGUID` varchar(255) DEFAULT NULL,
          `MedicationName` varchar(255) NOT NULL,
          `Dosage` varchar(255) DEFAULT NULL,
          `MedicationFor` varchar(255) DEFAULT NULL,
          `Frequency` int DEFAULT NULL,
          `Duration` int DEFAULT NULL,
          `DurationUnit` varchar(255) DEFAULT NULL,
          `IntakeTime` datetime DEFAULT NULL,
          `Status` enum('Upcoming','Taken','Missed') DEFAULT 'Upcoming',
          PRIMARY KEY (`ScheduleID`),
          KEY `PatientID` (`PatientID`),
          CONSTRAINT `medicationschedule_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE SET NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        "INSERT INTO `medicationschedule` (`ScheduleID`, `PatientID`, `PrescriptionGUID`, `MedicationName`, `Dosage`, `MedicationFor`, `Frequency`, `Duration`, `DurationUnit`, `IntakeTime`, `Status`) VALUES
        (1, 'PT-0001', '240d1d16-4b0e-11f0-b040-64f084c44b2a', 'Biogesic (Paracetamol)', '500mg Tablet', 'Headache and Fever', 3, 3, 'days', '2025-06-17 16:00:55', 'Upcoming'),
        (2, 'PT-0001', '240d1d16-4b0e-11f0-b040-64f084c44b2a', 'Biogesic (Paracetamol)', '500mg Tablet', 'Headache and Fever', 3, 3, 'days', '2025-06-18 00:00:55', 'Upcoming'),
        (3, 'PT-0002', '2412b1ec-4b0e-11f0-b040-64f084c44b2a', 'Neozep Forte', '1 Tablet', 'Colds', 2, 5, 'days', '2025-06-16 08:00:55', 'Taken'),
        (4, 'PT-0002', '2412b1ec-4b0e-11f0-b040-64f084c44b2a', 'Neozep Forte', '1 Tablet', 'Colds', 2, 5, 'days', '2025-06-17 12:00:55', 'Upcoming'),
        (5, 'PT-0003', '2417c479-4b0e-11f0-b040-64f084c44b2a', 'Sartan (Losartan)', '50mg Tablet', 'High Blood Pressure', 1, 30, 'days', '2025-06-16 20:00:55', 'Missed'),
        (6, 'PT-0008', '2421e6a2-4b0e-11f0-b040-64f084c44b2a', 'Kremil-S', '1 Tablet', 'Hyperacidity', 1, 1, 'weeks', '2025-06-15 08:00:55', 'Taken'),
        (7, 'PT-0008', '2421e6a2-4b0e-11f0-b040-64f084c44b2a', 'Kremil-S', '1 Tablet', 'Hyperacidity', 1, 1, 'weeks', '2025-06-16 08:00:55', 'Taken'),
        (8, 'PT-0008', '2421e6a2-4b0e-11f0-b040-64f084c44b2a', 'Kremil-S', '1 Tablet', 'Hyperacidity', 1, 1, 'weeks', '2025-06-17 04:00:55', 'Missed'),
        (9, 'PT-0008', '2421e6a2-4b0e-11f0-b040-64f084c44b2a', 'Kremil-S', '1 Tablet', 'Hyperacidity', 1, 1, 'weeks', '2025-06-18 04:00:55', 'Upcoming'),
        (10, 'PT-0015', '2433903b-4b0e-11f0-b040-64f084c44b2a', 'Allerta (Loratadine)', '10mg Tablet', 'Allergies', 1, 14, 'days', '2025-06-19 08:00:55', 'Upcoming'),
        (11, 'PT-0015', '2433903b-4b0e-11f0-b040-64f084c44b2a', 'Allerta (Loratadine)', '10mg Tablet', 'Allergies', 1, 14, 'days', '2025-06-20 08:00:55', 'Upcoming');",

        "CREATE TABLE `reports` (
          `ReportID` int NOT NULL AUTO_INCREMENT,
          `PatientID` varchar(255) DEFAULT NULL,
          `ScheduleID` int DEFAULT NULL,
          `DoctorID` varchar(255) DEFAULT NULL,
          `ReportDetails` text NOT NULL,
          `ReportStatus` enum('Inspect','Ongoing','Resolved') DEFAULT 'Inspect',
          `CreationTimestamp` timestamp NOT NULL DEFAULT current_timestamp(),
          `ReportDate` datetime NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`ReportID`),
          KEY `PatientID` (`PatientID`),
          KEY `ScheduleID` (`ScheduleID`),
          KEY `DoctorID` (`DoctorID`),
          CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE SET NULL,
          CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`ScheduleID`) REFERENCES `medicationschedule` (`ScheduleID`) ON DELETE SET NULL,
          CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`DoctorID`) REFERENCES `doctor` (`DoctorID`) ON DELETE SET NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

        "INSERT INTO `reports` (`ReportID`, `PatientID`, `ScheduleID`, `DoctorID`, `ReportDetails`, `ReportStatus`, `CreationTimestamp`, `ReportDate`) VALUES
        (1, 'PT-0003', 5, 'DT-0001', 'Patient Samantha Torres (Room 103) missed their scheduled dose of 50mg Tablet of Sartan (Losartan) (for High Blood Pressure). The dose was scheduled for 12 hours ago.', 'Inspect', '2025-06-17 08:00:55', '2025-06-16 20:00:55'),
        (2, 'PT-0008', 8, 'DT-0003', 'Patient Lucas Ocampo (Room 204) missed their scheduled dose of 1 Tablet of Kremil-S (for Hyperacidity). The dose was scheduled for 4 hours ago.', 'Ongoing', '2025-06-17 08:00:55', '2025-06-17 04:00:55');"
    ];

    foreach ($sql_commands as $command) {
        if ($conn->query($command) === FALSE) {
            die("Error executing query: " . $conn->error);
        }
    }
} 