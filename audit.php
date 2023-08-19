<?php
$label_mapping = [
    "sub_divisions_npas" => "NPAS Training",
    "sub_divisions_dsu" => "DSU Training",
    "sub_divisions_mpo" => "MPO Training",
    "driver_trainings_driving" => "Driver Training",
    "driver_trainings_tacad" => "TACAD Training",
    "use_of_force_trainings_psu" => "PSU Training",
    "use_of_force_trainings_firearms" => "Firearms Training",
    "additional_trainings_forensics" => "Forensics Training",
    "additional_trainings_fim" => "FIM Training",
    "additional_trainings_training_officer" => "Training Officer Qualification",
    "additional_trainings_medical" => "NPAS Training",
    "collarNumber" => "Collar Number",
    "name" => "Name",
    "unit" => "Unit",
    "rank" => "Rank",
    // Add more mappings as needed
];

function get_display_label($key) {
    global $label_mapping;
    return isset($label_mapping[$key]) ? $label_mapping[$key] : $key;
}

function parse_audit_message($message, $label_mapping) {
    $parts = explode(" ", $message);

    // If the message format is not as expected, return the original message
    if (count($parts) < 5 || $parts[0] != "Updated") {
        return $message;
    }

    $key = $parts[1];

    // Replace the key with its human-readable label
    if (array_key_exists($key, $label_mapping)) {
        $parts[1] = $label_mapping[$key];
    }

    // Join the parts back together to form the new message
    return implode(" ", $parts);
}

session_start();
include 'config.php';

// Define the number of logs per page
$logs_per_page = 20;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

// Calculate offset for the logs
$offset = ($current_page - 1) * $logs_per_page;

// Get the total number of logs across all three tables
$sql_count = "SELECT COUNT(*) as total_logs FROM (
                SELECT registration_date as log_date FROM registrations
                UNION ALL
                SELECT change_date FROM training_logs
                UNION ALL
                SELECT change_date FROM user_logs
            ) AS combined_logs";

$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_logs = $row_count['total_logs'];
$total_pages = ceil($total_logs / $logs_per_page);

// Fetch logs for the current page, ordered by date
$sql_logs = "SELECT * FROM (
    
SELECT 'Registration' as log_type, registration_date as log_date, registered_by_name as admin_name, registered_by_characterId as admin_characterId, registered_name as user_name, registered_characterId as user_characterId, registered_rank as details FROM registrations

    UNION ALL
    
SELECT 'Training' as log_type, change_date, admin_name, admin_characterId, user_name, user_characterId, CONCAT('Updated ', changed_training, ' from ', previous_training_value, ' to ', new_training_value) as details FROM training_logs

    UNION ALL
    
SELECT 'User Update' as log_type, change_date, admin_name, admin_charid, user_name, user_characterId, CONCAT('Updated ', changed_value, ' from ', previous_value, ' to ', new_value) as details FROM user_logs

) AS combined_logs
ORDER BY log_date DESC
LIMIT $logs_per_page OFFSET $offset";


$result_logs = $conn->query($sql_logs);
$logs = [];
while ($row = $result_logs->fetch_assoc()) {
    $logs[] = $row;
// Process each log's details field to make it more human-readable
foreach ($logs as &$log) {
    $log['details'] = parse_audit_message($log['details'], $label_mapping);
}
unset($log);  // Unset reference to last element

}

// Close the database connection
$conn->close();
?>

<!-- The provided HTML mockup goes here, with PHP code integrated to display the logs and pagination -->


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Audit Log</title>
</head>
<body>

<h2>Audit Log</h2>
<button class="button" onclick="location.href='main.php';">Back to Main</button>
<table>
    <tr>
        <th>Date</th>
        <th>Action</th>
        <th>Updating Officer</th>
        <th>Officer</th>
        <th>Details</th>
    </tr>
    <?php
    foreach ($logs as $log) {
        echo "<tr>";
        echo "<td>" . $log['log_date'] . "</td>";
        echo "<td>" . $log['log_type'] . "</td>";
        echo "<td><a href='user_profile.php?characterId=" . $log['admin_characterId'] . "' style='color: inherit;'>" . $log['admin_name'] . "</a></td>";
        echo "<td><a href='user_profile.php?characterId=" . $log['user_characterId'] . "' style='color: inherit;'>" . $log['user_name'] . "</a></td>";
        echo "<td>" . $log['details'] . "</td>";
        echo "</tr>";
    }
    ?>
</table>

<div class="pagination">
    <?php
    if ($current_page > 1) {
        echo '<a href="?page=' . ($current_page - 1) . '">&laquo;</a>';
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            echo '<a href="?page=' . $i . '" class="active">' . $i . '</a>';
        } else {
            echo '<a href="?page=' . $i . '">' . $i . '</a>';
        }
    }
    if ($current_page < $total_pages) {
        echo '<a href="?page=' . ($current_page + 1) . '">&raquo;</a>';
    }
    ?>
</div>

</body>
</html>