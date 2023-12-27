<?php
require_once 'config.php';

$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->select_db($dbname);

$sql = "CREATE TABLE IF NOT EXISTS  `registrations` (
  `id` int(11) NOT NULL,
  `registered_by_characterId` varchar(100) NOT NULL,
  `registered_by_name` varchar(255) NOT NULL,
  `registered_by_rank` enum('Chief Constable','Deputy Chief Constable','Assistant Chief Constable','Chief Superintendent','Superintendent','Chief Inspector','Inspector','Sergeant','Constable','Probationary Constable') NOT NULL,
  `registered_by_collarNumber` varchar(4) NOT NULL,
  `registered_by_unit` varchar(255) NOT NULL,
  `registered_characterId` varchar(100) NOT NULL,
  `registered_name` varchar(255) NOT NULL,
  `registered_rank` enum('Chief Constable','Deputy Chief Constable','Assistant Chief Constable','Chief Superintendent','Superintendent','Chief Inspector','Inspector','Sergeant','Constable','Probationary Constable') NOT NULL,
  `registered_collarNumber` char(4) NOT NULL,
  `registered_unit` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table registrations created successfully";
} else {
    echo "Error creating table users: " . $conn->error . "<br>";
}

$sql = "CREATE TABLE IF NOT EXISTS  `trainings` (
  `characterId` varchar(255) NOT NULL,
  `sub_divisions_npas` enum('No','Basic','Advanced') NOT NULL DEFAULT 'No',
  `sub_divisions_dsu` enum('No','Basic','Advanced') NOT NULL DEFAULT 'No',
  `sub_divisions_mpo` enum('Yes','No') NOT NULL DEFAULT 'No',
  `driver_trainings_driving` enum('Standard','Advanced') NOT NULL DEFAULT 'Standard',
  `driver_trainings_tacad` enum('Yes','No') NOT NULL DEFAULT 'No',
  `use_of_force_trainings_psu` enum('Level 4','Level 3','Level 2','Level 1') NOT NULL DEFAULT 'Level 4',
  `use_of_force_trainings_firearms` enum('OST','Taser','FTO','T/AFO','AFO','T/SFO','SFO','T/CT-SFO','CT-SFO') NOT NULL DEFAULT 'OST',
  `additional_trainings_forensics` enum('Yes','No') NOT NULL DEFAULT 'No',
  `additional_trainings_fim` enum('Yes','No') NOT NULL DEFAULT 'No',
  `additional_trainings_training_officer` enum('Yes','No') NOT NULL DEFAULT 'No',
  `additional_trainings_medical` enum('No','Basic','Advanced') NOT NULL DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table trainings created successfully";
} else {
    echo "Error creating table users: " . $conn->error . "<br>";
}

$sql = "CREATE TABLE IF NOT EXISTS  `training_logs` (
  `id` int(11) NOT NULL,
  `admin_characterId` varchar(255) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `admin_rank` varchar(255) NOT NULL,
  `admin_collarNumber` char(4) NOT NULL,
  `admin_unit` varchar(255) NOT NULL,
  `user_characterId` varchar(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_rank` varchar(255) NOT NULL,
  `user_collarNumber` char(4) NOT NULL,
  `user_unit` varchar(255) NOT NULL,
  `changed_training` varchar(255) NOT NULL,
  `previous_training_value` varchar(255) NOT NULL,
  `new_training_value` varchar(255) NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `whitelisted` enum('No','Yes') NOT NULL DEFAULT 'No',
  `discord_whitelisted` enum('No','Yes') NOT NULL DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table training_logs created successfully";
} else {
    echo "Error creating table users: " . $conn->error . "<br>";
}

$sql = "CREATE TABLE IF NOT EXISTS  `users` (
  `characterId` varchar(255) NOT NULL,
  `rank` enum('Chief Constable','Deputy Chief Constable','Assistant Chief Constable','Chief Superintendent','Superintendent','Chief Inspector','Inspector','Sergeant','Constable','Probationary Constable') NOT NULL,
  `collarNumber` varchar(4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` enum('Police Command','Frontline','Response','Recruitment & Development','Standards','Tactical Operations','Roads Policing Unit','Firearms','CID','Policing Operations','NPAS','DSU','MPO','PSU','Archive') NOT NULL DEFAULT 'Recruitment & Development',
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully";
} else {
    echo "Error creating table users: " . $conn->error . "<br>";
}

$sql = "CREATE TABLE IF NOT EXISTS `user_logs` (
    `id` int(11) NOT NULL,
    `admin_charid` varchar(255) NOT NULL,
    `admin_name` varchar(255) NOT NULL,
    `admin_rank` enum('Chief Constable','Deputy Chief Constable','Assistant Chief Constable','Chief Superintendent','Superintendent','Chief Inspector','Inspector','Sergeant','Constable','Probationary Constable') NOT NULL,
    `admin_collarNumber` char(4) NOT NULL,
    `admin_unit` varchar(255) NOT NULL,
    `user_characterId` varchar(255) NOT NULL,
    `user_name` varchar(255) NOT NULL,
    `user_rank` enum('Chief Constable','Deputy Chief Constable','Assistant Chief Constable','Chief Superintendent','Superintendent','Chief Inspector','Inspector','Sergeant','Constable','Probationary Constable') NOT NULL,
    `user_collarNumber` char(4) NOT NULL,
    `user_unit` varchar(255) NOT NULL,
    `changed_value` varchar(255) NOT NULL,
    `previous_value` varchar(255) NOT NULL,
    `new_value` varchar(255) NOT NULL,
    `change_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
  
if ($conn->query($sql) === TRUE) {
    echo "User logs table created successfully";
} else {
    echo "Error creating user logs table: " . $conn->error;
}

$conn->close();
unlink(__FILE__);
?>