<?php
require_once 'config.php';

$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->select_db($dbname);

$sql = "CREATE TABLE IF NOT EXISTS users (
    characterId VARCHAR(255) PRIMARY KEY,
    rank VARCHAR(255),
    collarNumber VARCHAR(255),
    name VARCHAR(255),
    password VARCHAR(255)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>