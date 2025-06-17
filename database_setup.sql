-- MedTrack Database Setup (Admins)

-- This script creates the 'admins' table and adds an 'IsArchived' column to 'personnel'.
-- Run this script on your `medtrackdb` database.

-- Step 1: Create the 'admins' table
CREATE TABLE IF NOT EXISTS `admins` (
  `AdminID` VARCHAR(10) NOT NULL,
  `AdminName` VARCHAR(255) NOT NULL,
  `Email` VARCHAR(255) NOT NULL UNIQUE,
  `Password` VARCHAR(255) NOT NULL,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expires` DATETIME DEFAULT NULL,
  PRIMARY KEY (`AdminID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 2: Add 'IsArchived' column to the 'personnel' table if it doesn't exist
ALTER TABLE `personnel`
ADD COLUMN `IsArchived` BOOLEAN NOT NULL DEFAULT FALSE AFTER `ProfilePicture`;

-- Step 3: Insert the 3 default admin accounts
-- The password for all admins is 'adminpass123'.
INSERT IGNORE INTO `admins` (`AdminID`, `AdminName`, `Email`, `Password`) VALUES
('AD-001', 'Admin Uno', 'admin1@medtrack.ph', '$2y$10$wSgS5FB.x.rKz1QjcyL4.uD9V3L5W2wO/qX6bJ3y5t8r7N9M4o.O6'),
('AD-002', 'Admin Dos', 'admin2@medtrack.ph', '$2y$10$fL4U7V/E9qgN.Z2n0tH8AeyLk5wY.u3xM1v6j5d4x4G/J.wF6pH2.'),
('AD-003', 'Admin Tres', 'admin3@medtrack.ph', '$2y$10$dK5V.nG8P6qO.y7X8z9v9pQRz.sB/y7bQ5z9m8g6a6I/L.yG7qH3.'); 