<?php
include 'config.php';

$characterId = $_POST['characterId'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE characterId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $characterId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        session_start();
        $_SESSION['characterId'] = $characterId;
        $_SESSION['rank'] = $row['rank']; // Store the rank in the session
        $_SESSION['name'] = $row['name']; // Store the name in the session
        $_SESSION['collarNumber'] = $row['collarNumber']; // Store the collar number in the session
        $_SESSION['unit'] = $row['unit']; // Store the unit in the session
        header('Location: main.php');
        exit();
    } else {
        echo 'Invalid character ID or password.';
    }
} else {
    echo 'Invalid character ID or password.';
}
?>