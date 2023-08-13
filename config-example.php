<?php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "mydatabase";

// Create connection without selecting the database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Try to select the database
$db_select = @mysqli_select_db($conn, $dbname);
if (!$db_select) {
    // If the database doesn't exist, create it
    $create_db_sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($create_db_sql) === TRUE) {
        // Now select the newly created database
        $db_select = @mysqli_select_db($conn, $dbname);
        if (!$db_select) {
            die("Error selecting database: " . $conn->error);
        }
    } else {
        die("Error creating database: " . $conn->error);
    }
}
?>