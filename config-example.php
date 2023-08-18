<?php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "mydatabase";


$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$create_db_sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($create_db_sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}


if (!mysqli_select_db($conn, $dbname)) {
    die("Error selecting database: " . $conn->error);
}

?>