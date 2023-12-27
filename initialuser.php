<?php
// Include your database configuration file
include 'config.php';

// User details - replace these with your desired initial user details
$rank = "Chief Constable"; // e.g., 'Chief Constable'
$collarNumber = "1000"; // e.g., '1234'
$name = "Toby Daniels"; // e.g., 'John Doe'
$characterId = "11615"; // e.g., 'JDOE01'
$password = "1"; // Choose a strong password
$unit = "Frontline"; // e.g., 'Police Command'

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// SQL query to insert the new user
$sql = "INSERT INTO users (rank, collarNumber, name, characterId, password, unit) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $rank, $collarNumber, $name, $characterId, $hashedPassword, $unit);

// Execute the query
if ($stmt->execute()) {
    echo "New user created successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement
$stmt->close();

// Close the connection
$conn->close();
unlink(__FILE__);
?>
