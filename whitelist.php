<?php
session_start();
include 'config.php';

// Fetch data for the in-game and Discord whitelisting tables from the training_logs table
$sql_in_game = "SELECT * FROM training_logs WHERE whitelisted = 'No' ORDER BY change_date ASC";
$sql_discord = "SELECT * FROM training_logs WHERE discord_whitelisted = 'No' ORDER BY change_date ASC";

$result_in_game = $conn->query($sql_in_game);
$result_discord = $conn->query($sql_discord);

$in_game_logs = [];
$discord_logs = [];
while ($row = $result_in_game->fetch_assoc()) {
    $in_game_logs[] = $row;
}
while ($row = $result_discord->fetch_assoc()) {
    $discord_logs[] = $row;
}

// Close the database connection
$conn->close();



function getDetails($log) {
    // Logic for sub_divisions_npas
    if ($log['changed_training'] == 'sub_divisions_npas' &&
        $log['new_training_value'] == 'Basic') {
        return "Add NPAS 1";
    }
    if ($log['changed_training'] == 'sub_divisions_npas' &&
        $log['new_training_value'] == 'Advanced') {
        return "Add NPAS 2";
    }
    if ($log['changed_training'] == 'sub_divisions_npas' &&
        $log['new_training_value'] == 'No') {
        return "Remove NPAS";
    }

    // Logic for sub_divisions_dsu
    if ($log['changed_training'] == 'sub_divisions_dsu' &&
        $log['new_training_value'] == 'Basic') {
        return "Add DSU 1";
    }
    if ($log['changed_training'] == 'sub_divisions_dsu' &&
        $log['new_training_value'] == 'Advanced') {
        return "Add DSU 2";
    }
    if ($log['changed_training'] == 'sub_divisions_dsu' &&
        $log['new_training_value'] == 'No') {
        return "Remove DSU";
    }

    // Logic for sub_divisions_mpo
    if ($log['changed_training'] == 'sub_divisions_mpo' &&
        $log['new_training_value'] == 'Yes') {
        return "Add MPO 2";
    }
    if ($log['changed_training'] == 'sub_divisions_mpo' &&
        $log['new_training_value'] == 'Yes') {
        return "Remove MPO";
    }

    // Logic for driver_trainings_driving
    if ($log['changed_training'] == 'driver_trainings_driving' &&
        $log['previous_training_value'] == 'Advanced' &&
        $log['new_training_value'] == 'Standard') {
        return "Remove Traffic Whitelisting";
    }
    if ($log['changed_training'] == 'driver_trainings_driving' &&
        $log['new_training_value'] == 'Advanced') {
        return "Add Traffic 1";
    }

    // Logic for use_of_force_trainings_psu
    if ($log['changed_training'] == 'use_of_force_trainings_psu' &&
        $log['new_training_value'] == 'Level 4') {
        return "Remove PSU Whitelisting";
    }
    if ($log['changed_training'] == 'use_of_force_trainings_psu' &&
        $log['new_training_value'] == 'Level 3') {
        return "Add PSU 1";
    }
    if ($log['changed_training'] == 'use_of_force_trainings_psu' &&
        $log['new_training_value'] == 'Level 2') {
        return "Add PSU 2";
    }
    if ($log['changed_training'] == 'use_of_force_trainings_psu' &&
        $log['new_training_value'] == 'Level 1') {
        return "Add PSU 3";
    }

    // Logic for use_of_force_tranings_firearms
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'OST') {
        return "Remove Firearms & Taser Whitelisting";
    }    
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'Taser') {
        return "Add Taser 1";
    }    
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['previous_training_value'] == 'OST' &&
        $log['new_training_value'] == 'FTO') {
        return "Add Taser & Firearms Whitelisting";
    }
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'FTO') {
        return "Add Firearms 1";
    }
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'T/AFO') {
        return "Add Firearms 2";
    }
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'AFO') {
        return "Add Firearms 2";
    }
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'T/SFO') {
        return "Add Firearms 3";
    }
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'SFO') {
        return "Add Firearms 3";
    }
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'T/CT-SFO') {
        return "Add Firearms 4";
    }
    if ($log['changed_training'] == 'use_of_force_tranings_firearms' &&
        $log['new_training_value'] == 'CT-SFO') {
        return "Add Firearms 4";
    }

    // Logic for additional_trainings_forensics
    if ($log['changed_training'] == 'additional_trainings_forensics' &&
        $log['new_training_value'] == 'Yes') {
        return "Add Forensics 1";
    }
    if ($log['changed_training'] == 'additional_trainings_forensics' &&
        $log['new_training_value'] == 'No') {
        return "Remove Forensics";
    }
    
    // Logic for additional_trainings_fim
    if ($log['changed_training'] == 'additional_trainings_fim' &&
        $log['new_training_value'] == 'Yes') {
        return "Add Dispatcher 1";
    }
    if ($log['changed_training'] == 'additional_trainings_fim' &&
        $log['new_training_value'] == 'No') {
        return "Remove Dispatcher";
    }
    
    // Logic for additional_trainings_training_officer
    if ($log['changed_training'] == 'additional_trainings_training_officer' &&
        $log['new_training_value'] == 'Yes') {
        return "Add Training 1";
    }
    if ($log['changed_training'] == 'additional_trainings_training_officer' &&
        $log['new_training_value'] == 'No') {
        return "Remove Training";
    }
    
    // Logic for additional_trainings_medical
    if ($log['changed_training'] == 'additional_trainings_medical' &&
        $log['new_training_value'] == 'Basic') {
        return "Add First Aid 1";
    }
    if ($log['changed_training'] == 'additional_trainings_medical' &&
        $log['new_training_value'] == 'Advanced') {
        return "Add First Aid 2";
    }
    if ($log['changed_training'] == 'additional_trainings_medical' &&
        $log['new_training_value'] == 'No') {
        return "Remove First Aid";
    }


    // Add more conditions as needed
    return "";  // Default return if no conditions match
}

foreach ($in_game_logs as &$log) {
    $log['details'] = getDetails($log);
}

foreach ($discord_logs as &$log) {
    $log['details'] = getDetails($log);
}

if (isset($_POST['submit_whitelisting'])) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Iterate over the POST data to update the whitelisting statuses
    foreach ($_POST as $key => $value) {
        // Check for discord_whitelisted first due to substring issue
        if (strpos($key, "discord_whitelisted_") !== false) {
            $discord_log_id = str_replace("discord_whitelisted_", "", $key);
            $discord_whitelisted_status = ($value == "on" ? "Yes" : "No");
            $sql_update_discord = "UPDATE training_logs SET discord_whitelisted='$discord_whitelisted_status' WHERE id=$discord_log_id";
            $conn->query($sql_update_discord);
        }
        // Then check for whitelisted
        elseif (strpos($key, "whitelisted_") !== false) {
            $whitelisted_log_id = str_replace("whitelisted_", "", $key);
            $whitelisted_status = ($value == "on" ? "Yes" : "No");
            $sql_update_whitelisted = "UPDATE training_logs SET whitelisted='$whitelisted_status' WHERE id=$whitelisted_log_id";
            $conn->query($sql_update_whitelisted);
        }
    }
    
    // Close the database connection
    $conn->close();
    
    // Refresh the page to reflect the changes
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_whitelisting"])) {
    foreach ($_POST as $key => $value) {
        $whitelisted_status = ($value == "on") ? "Yes" : "No";
        
        // Splitting the checkbox name string and extracting the numeric portion (log ID)
        $parts = explode('_', $key);
        $log_id = end($parts);
        
        // Determine if the checkbox is from the in-game table or the Discord table
        if (strpos($key, "whitelisted_") === 0) {
            $sql_update = "UPDATE training_logs SET whitelisted='$whitelisted_status' WHERE id=$log_id";
        } elseif (strpos($key, "discord_whitelisted_") === 0) {
            $sql_update = "UPDATE training_logs SET discord_whitelisted='$whitelisted_status' WHERE id=$log_id";
        } else {
            continue;  // Skip any other POST values
        }
        
        $conn->query($sql_update);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<style>
        body {
            background-color: #333; /* dark grey */
            color: #fff; /* white */
            font-family: Arial, sans-serif;
            text-align: center;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 90%;
        }
        th, td {
            padding: 10px;
            border: 1px solid #fff;
        }
        th {
            background-color: #073763; /* dark blue */
        }
        .pagination {
            display: inline-block;
        }
        .pagination a {
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            transition: background-color .3s;
        }
        .pagination a.active {
            background-color: #073763;
            color: white;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
            color: black;
        }
        .button {
            padding: 10px 20px;
            background-color: #555; /* dark grey */
            color: #fff; /* White */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }
    </style>
    <title>Whitelisting Management</title>
    <link rel="stylesheet" type="text/css" href="styles.css">  <!-- Placeholder for potential external stylesheet -->
</head>
<body>

<h2>Whitelisting Management</h2>
<button class="button" onclick="location.href='main.php';">Back to Main</button>
<button class="button" onclick="">Show Completed</button>
<form method='post' action=''>

<!-- In-Game Whitelisting Table -->
<table style="width: 48%; float: left;">
    <caption>In-Game Whitelisting</caption>
    <tr>
        <th>Date</th>
        <th>Updating Officer</th>
        <th>Officer</th>
        <th>Details</th>
        <th>Whitelisted</th>
    </tr>
    <?php
    foreach ($in_game_logs as $log) {
        echo "<tr>";
        echo "<td>" . $log['change_date'] . "</td>";
        echo "<td>" . $log['admin_name'] . "</td>";
        echo "<td>" . $log['user_name'] . "</td>";
        echo "<td>" . $log['details'] . "</td>";
        echo "<td><input type='checkbox' name='whitelisted_" . $log['id'] . "' " . ($log['whitelisted'] == 'Yes' ? "checked" : "") . "></td>";
        echo "</tr>";
    }
    ?>
</table>

<!-- Discord Whitelisting Table -->
<table style="width: 48%; float: right;">
    <caption>Discord Whitelisting</caption>
    <tr>
        <th>Date</th>
        <th>Updating Officer</th>
        <th>Officer</th>
        <th>Details</th>
        <th>Whitelisted</th>
    </tr>
    <?php
    foreach ($discord_logs as $log) {
        echo "<tr>";
        echo "<td>" . $log['change_date'] . "</td>";
        echo "<td>" . $log['admin_name'] . "</td>";
        echo "<td>" . $log['user_name'] . "</td>";
        echo "<td>" . $log['details'] . "</td>";
        echo "<td><input type='checkbox' name='discord_whitelisted_" . $log['id'] . "' " . ($log['discord_whitelisted'] == 'Yes' ? "checked" : "") . "></td>";
        echo "</tr>";
    }
    ?>
</table>


<input type='submit' name='submit_whitelisting' value='Update Whitelisting'>
</form>
</body>
</html>