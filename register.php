<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['characterId'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Define the ranks that can be registered based on the logged-in user's rank
$allowedRanks = array(
    'Chief Constable' => array('Deputy Chief Constable', 'Assistant Chief Constable', 'Chief Superintendent', 'Superintendent', 'Chief Inspector', 'Inspector', 'Sergeant', 'Constable', 'Probationary Constable'),
    'Deputy Chief Constable' => array('Assistant Chief Constable', 'Chief Superintendent', 'Superintendent', 'Chief Inspector', 'Inspector', 'Sergeant', 'Constable', 'Probationary Constable'),
    'Assistant Chief Constable' => array('Chief Superintendent', 'Superintendent', 'Chief Inspector', 'Inspector', 'Sergeant', 'Constable', 'Probationary Constable'),
    'Chief Superintendent' => array('Superintendent', 'Chief Inspector', 'Inspector', 'Sergeant', 'Constable', 'Probationary Constable'),
    'Superintendent' => array('Chief Inspector', 'Inspector', 'Sergeant', 'Constable', 'Probationary Constable'),
    'Chief Inspector' => array('Inspector', 'Sergeant', 'Constable', 'Probationary Constable'),
    'Inspector' => array('Sergeant', 'Constable', 'Probationary Constable'),
    'Sergeant' => array('Constable', 'Probationary Constable'),
    'Constable' => array(),
    'Probationary Constable' => array(),
);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form fields
    $rank = $_POST['rank'] ?? '';
    $collarNumber = $_POST['collarNumber'] ?? '';
    $name = $_POST['name'] ?? '';
    $characterId = $_POST['characterId'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($rank) || empty($name) || empty($characterId) || empty($password)) {
        echo 'All fields are required.';
        exit();
    }

    $unit = $_POST['unit'] ?? '';

    if (empty($unit)) {
        echo 'Unit is required.';
        exit();
    }

    // Check if the logged-in user can register the selected rank
    if (!in_array($rank, $allowedRanks[$_SESSION['rank']])) {
        echo 'You do not have permission to register this rank.';
        exit();
    }

    // Generate a unique 4-digit collar number for Probationary Constables
    if ($rank === 'Probationary Constable') {
        do {
            $collarNumber = generateCollarNumber();
        } while (collarNumberExists($collarNumber));
    }

    // Hash the password before storing it in the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user data (including hashed password) into the database
    $sql = "INSERT INTO users (rank, collarNumber, name, characterId, password, unit) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $rank, $collarNumber, $name, $characterId, $hashedPassword, $unit);

    if ($stmt->execute()) {
    // Insert default training data for the new user
    $sql_trainings = "INSERT INTO trainings (
        characterId, 
        sub_divisions_npas, 
        sub_divisions_dsu, 
        sub_divisions_mpo, 
        driver_trainings_driving, 
        driver_trainings_tacad, 
        use_of_force_trainings_psu, 
        use_of_force_trainings_firearms, 
        additional_trainings_forensics, 
        additional_trainings_fim, 
        additional_trainings_training_officer, 
        additional_trainings_medical
    ) VALUES (
        ?, 
        'No', 
        'No', 
        'No', 
        'Standard', 
        'No', 
        'Level 4',
        'OST', 
        'No', 
        'No', 
        'No', 
        'No'
    )";
    $stmt_trainings = $conn->prepare($sql_trainings);
    $stmt_trainings->bind_param('s', $characterId);
    
    if (!$stmt_trainings->execute()) {
        echo 'Error populating training data.';
        exit();
    }

        // Get the last inserted user's ID
        $registered_characterId = $characterId;

        // Get the logged-in user's information
        $registered_by_characterId = $_SESSION['characterId'];
        $registered_by_name = $_SESSION['name'];
        $registered_by_rank = $_SESSION['rank'];
        $registered_by_collarNumber = $_SESSION['collarNumber'];
        $registered_by_unit = $_SESSION['unit'];

        // Log the registration information
        $sql_log = "INSERT INTO registrations (registered_by_characterId, registered_by_name, registered_by_rank, registered_by_collarNumber, registered_by_unit, registered_characterId, registered_name, registered_rank, registered_collarNumber, registered_unit, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->bind_param('ssssssssss', $registered_by_characterId, $registered_by_name, $registered_by_rank, $registered_by_collarNumber, $registered_by_unit, $registered_characterId, $name, $rank, $collarNumber, $unit);

        if ($stmt_log->execute()) {
            echo "User registered successfully!";
        } else {
            echo "Error: " . $stmt_log->error;
        }

        $stmt_log->close();

    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();

function generateCollarNumber()
{
    // Generate a random 4-digit collar number for Probationary Constables
    return str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function collarNumberExists($collarNumber)
{
    global $conn;
    $sql = "SELECT collarNumber FROM users WHERE collarNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $collarNumber);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="stylesheet.css">    
    <title>Registration</title>
    <script>
    function handleRankChange() {
        var rankSelect = document.getElementById('rank');
        var unitSelect = document.getElementById('unit');
        var collarNumberInput = document.getElementById('collarNumber');

        if (rankSelect.value === 'Probationary Constable') {
            unitSelect.value = 'Recruitment & Development';
            unitSelect.setAttribute('disabled', 'disabled'); // Disable unit select

            // Generate a random 4-digit collar number for Probationary Constables
            var randomNumber = Math.floor(Math.random() * 9000) + 1000;
            collarNumberInput.value = randomNumber;
            collarNumberInput.disabled = true;

            // Create a hidden input to send the unit value to server
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'unit');
            hiddenInput.setAttribute('value', 'Recruitment & Development');
            document.querySelector('form').appendChild(hiddenInput);
        } else {
            unitSelect.disabled = false;
            collarNumberInput.disabled = false;

            // If exists, remove the hidden input for unit
            var hiddenInput = document.querySelector('input[type="hidden"][name="unit"]');
            if (hiddenInput) {
                hiddenInput.remove();
            }
        }
    }
    </script>
</head>
<body>
    <div class="registration-form">
        <h2>Register a New User</h2>
        <button class="button" onclick="location.href='main.php';">Back to Main</button>
        <form action="register.php" method="post">
            <input type="text" name="characterId" placeholder="Character ID" required />
            <input type="text" name="name" placeholder="Name" required />
            <select name="rank" id="rank" onchange="handleRankChange()" required>
                <option value="">Select Rank</option>
                <?php
                // Display only the allowed ranks based on the logged-in user's rank
                $loggedInUserRank = $_SESSION['rank'];
                foreach ($allowedRanks[$loggedInUserRank] as $allowedRank) {
                    echo "<option value=\"$allowedRank\">$allowedRank</option>";
                }
                ?>
            </select>
            <select name="unit" id="unit" required>
                <option value="">Select Unit</option>
                <option value="Police Command">Police Command</option>
                <option value="Frontline">Frontline</option>
                <option value="Response">Response</option>
                <option value="Recruitment & Development">Recruitment & Development</option>
                <option value="Standards">Standards</option>
                <option value="Tactical Operations">Tactical Operations</option>
                <option value="Roads Policing Unit">Roads Policing Unit</option>
                <option value="Firearms">Firearms</option>
                <option value="CID">CID</option>
                <option value="Policing Operations">Policing Operations</option>
                <option value="NPAS">NPAS</option>
                <option value="DSU">DSU</option>
                <option value="MPO">MPO</option>
                <option value="PSU">PSU</option>
            </select>
            <input type="text" name="collarNumber" id="collarNumber" placeholder="Collar Number" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="submit" value="Register" />
        </form>
    </div>
</body>
</html>