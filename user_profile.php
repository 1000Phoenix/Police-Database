<?php
session_start();

include 'config.php';

// Function to fetch user data by characterId
function getUserData($conn, $characterId)
{
    $sql = "SELECT users.*, trainings.* FROM users LEFT JOIN trainings ON users.characterId = trainings.characterId WHERE users.characterId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $characterId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    return null;
}

// Check if the user has permission to edit this profile
$canEdit = false;
$rankOrder = array(
    'Chief Constable',
    'Deputy Chief Constable',
    'Assistant Chief Constable',
    'Chief Superintendent',
    'Superintendent',
    'Chief Inspector',
    'Inspector',
    'Sergeant',
    'Constable',
    'Probationary Constable'
);

$units = array(
    'Police Command',
    'Frontline',
    'Response',
    'Recruitment & Development',
    'Standards',
    'Tactical Operations',
    'Roads Policing Unit',
    'Firearms',
    'CID',
    'Policing Operations',
    'NPAS',
    'DSU',
    'MPO',
    'PSU',
    'Archive',
);

if (isset($_GET['characterId'])) {
    $characterId = $_GET['characterId'];
    $user = getUserData($conn, $characterId);

    // Check for user permission here...
    $rankIndex = array_search($_SESSION['rank'], $rankOrder);
    $userRankIndex = array_search($user['rank'], $rankOrder);

    // Additional check for user permission
    $isAccessibleRank = $rankIndex !== false && $userRankIndex !== false && $rankIndex < $userRankIndex;

    if ($rankIndex !== false && $userRankIndex !== false && $rankIndex < $userRankIndex) {
        $canEdit = true;
    }
}

// Handle form submission for editing profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && $canEdit) {
    // Check if collarNumber and name form is submitted
    if (isset($_POST['collarNumber']) && isset($_POST['name'])) {
        // Update the collarNumber and name in the users table
        $newCollarNumber = mysqli_real_escape_string($conn, $_POST['collarNumber']);
        $newName = mysqli_real_escape_string($conn, $_POST['name']);

        // Check if the user has permission to edit collar number and name
        if ($isAccessibleRank) {
            // Prepare SQL statement to update the collar number and name in the users table
            $updateCollarNameSql = "UPDATE users SET collarNumber = ?, name = ? WHERE characterId = ?";
            $stmt = $conn->prepare($updateCollarNameSql);

            if (!$stmt) {
                echo "Error preparing statement: " . $conn->error;
                exit();
            }

            // Bind parameters
            $stmt->bind_param('sss', $newCollarNumber, $newName, $characterId);

            // Execute the statement
            if (!$stmt->execute()) {
                echo "Error executing statement: " . $stmt->error;
                exit();
            }

            // Update the user's collar number and name in the $user array after the update.
            $user['collarNumber'] = $newCollarNumber;
            $user['name'] = $newName;

            // Close the statement
            $stmt->close();
        } else {
            echo "You don't have permission to edit collar number and name.";
            exit();
        }
    }

    // Get new rank and unit from POST
    $newRank = isset($_POST['rank']) ? mysqli_real_escape_string($conn, $_POST['rank']) : $user['rank'];
    $newUnit = isset($_POST['unit']) ? mysqli_real_escape_string($conn, $_POST['unit']) : $user['unit'];

    // Retrieve and sanitize values for trainings
    $training_cols = ['sub_divisions_npas', 'sub_divisions_dsu', 'sub_divisions_mpo', 'driver_trainings_driving', 'driver_trainings_tacad', 'use_of_force_trainings_psu', 'use_of_force_trainings_firearms', 'additional_trainings_forensics', 'additional_trainings_fim', 'additional_trainings_training_officer', 'additional_trainings_medical'];
    $training_values = [];
    $training_log_values = [];

    foreach ($training_cols as $col_name) {
        $old_value = $user[$col_name];
        $new_value = isset($_POST[$col_name]) ? mysqli_real_escape_string($conn, $_POST[$col_name]) : $user[$col_name];

        $training_values[] = $new_value;

        if ($old_value !== $new_value) {
            $training_log_values[] = [
                'training_name' => $col_name,
                'old_value' => $old_value,
                'new_value' => $new_value,
            ];
        }
    }

    // Prepare SQL statements for updating trainings and user's rank and unit in the users table
    $updateTrainingsSql = "UPDATE trainings SET sub_divisions_npas=?, sub_divisions_dsu=?, sub_divisions_mpo=?, driver_trainings_driving=?, driver_trainings_tacad=?, use_of_force_trainings_psu=?, use_of_force_trainings_firearms=?, additional_trainings_forensics=?, additional_trainings_fim=?, additional_trainings_training_officer=?, additional_trainings_medical=? WHERE characterId=?";
    $stmt = $conn->prepare($updateTrainingsSql);

    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }

    // Prepare the type definition string based on the number of training_values elements
    $typeString = str_repeat('s', count($training_values)) . 's';

    // Combine the type definition string and training_values array for bind_param
    $params = array_merge([$typeString], $training_values, [$characterId]);

    // Bind parameters for trainings table
    $stmt->bind_param(...$params);

    // Execute the statement for trainings table
    if (!$stmt->execute()) {
        echo "Error executing statement: " . $stmt->error;
        exit();
    }

    // Close the statement
    $stmt->close();


    // Log the changes
    foreach ($training_log_values as $log_value) {
        $sql_log = "INSERT INTO training_logs (admin_characterId, admin_name, admin_rank, admin_collarNumber, admin_unit, user_characterId, user_name, user_rank, user_collarNumber, user_unit, changed_training, previous_training_value, new_training_value, change_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt_log = $conn->prepare($sql_log);

        if ($stmt_log) {
            $stmt_log->bind_param('sssssssssssss', $_SESSION['characterId'], $_SESSION['name'], $_SESSION['rank'], $_SESSION['collarNumber'], $_SESSION['unit'], $characterId, $user['name'], $user['rank'], $user['collarNumber'], $user['unit'], $log_value['training_name'], $log_value['old_value'], $log_value['new_value']);

            if (!$stmt_log->execute()) {
                echo "Error executing log statement: " . $stmt_log->error;
            }

            $stmt_log->close();
        } else {
            echo "Error preparing log statement: " . $conn->error;
        }
    }

    // Re-load the $user array after the update.
    $user = getUserData($conn, $characterId);

    if (isset($_POST['rank'], $_POST['unit'], $_POST['name'], $_POST['collarNumber'])) {
        $newRank = $_POST['rank'];
        $newUnit = $_POST['unit'];
        $newName = $_POST['name'];
        $newCollarNumber = $_POST['collarNumber'];
    
        // Code to update the rank and unit
        $updateRankUnitSql = "UPDATE users SET rank = ?, unit = ? WHERE characterId = ?";
        $stmt = $conn->prepare($updateRankUnitSql);
    
        if (!$stmt) {
            echo "Error preparing statement: " . $conn->error;
            exit();
        }
    
        // Bind parameters
        $stmt->bind_param('sss', $newRank, $newUnit, $characterId);
    
        // Execute the statement
        if (!$stmt->execute()) {
            echo "Error executing statement: " . $stmt->error;
            exit();
        }
    
        // Update the user's rank and unit in the $user array after the update.
        $user['rank'] = $newRank;
        $user['unit'] = $newUnit;
    
        // Close the statement
        $stmt->close();
    
        // Additional code to update the name and collar number
        $sql = "UPDATE users SET name = ?, collarNumber = ? WHERE characterId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $newName, $newCollarNumber, $characterId);
        $stmt->execute();
        $stmt->close();
    }
} 
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <style>
        body {
            background-color: #333; /* dark grey */
            color: #fff; /* white */
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .profile {
            margin: 40px auto;
            max-width: 600px;
            padding: 20px;
            border: 2px solid #555;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-image {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin: 0 auto 20px auto;
        }

        .profile h2 {
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .training-section {
            text-align: left;
            margin-bottom: 20px;
        }

        .training-section h3 {
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .training-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .training-item label {
            flex: 1;
        }

        .training-item span {
            padding: 5px 10px;
            border-radius: 5px;
            background-color: #555; /* dark grey */
            color: #fff; /* white */
            transition: all 0.3s;
        }

        .edit-button:hover {
            background-color: #777;
        }
        select {
            padding: 5px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #555;
            border-radius: 5px;
            color: #fff;
            margin: 5px 0;
            width: 100%;
        }
        select.rank-select {
            width: auto;
        }
        select.unit-select {
            width: auto;
        }
        select option {
            background-color: #555;
            color: white;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #555; /* dark grey */
            color: #fff; /* white */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }

        input[type="submit"]:hover {
            background-color: #777;
        }

        .training-item span:hover {
            background-color: #777;
        }
        .back-button {
            padding: 10px 20px;
            background-color: #555; /* dark grey */
            color: #fff; /* White */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
            text-decoration: none
        }        
        .edit-button {
            display: <?php echo $isAccessibleRank ? 'inline-block' : 'none'; ?>;
            padding: 10px 20px;
            background-color: #555; /* dark grey */
            color: #fff; /* White */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
            text-decoration: none
        }
        .training-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .training-card {
            flex: 0 0 calc(33% - 10px);
            box-sizing: border-box;
            margin: 5px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input {
            display: block;
            background-color: #555;
            border-radius: 5px;
            box-sizing: border-box;
            color: #fff
        }
    </style>
</head>
<body>
    <div class="profile">
        <h2>User Profile</h2>
        <img class="profile-image" src="images/Profiles/<?php echo $user['characterId']; ?>.png" alt="<?php echo $user['name']; ?>" />
        <div class="button-container">
            <!-- 'Edit' Button -->
            <button id="editButton" class="edit-button" onclick="toggleEditMode()">Edit</button>

            <!-- Back to Main Button -->
            <a href="main.php" class="back-button">Back to Main</a>
        </div>

        <form id="editForm" method="post">
            <!-- Name -->
            <p>
                <strong>Name:</strong> 
                <span class="display-mode"><?php echo $user['name']; ?></span>
                <input type="text" class="edit-mode" name="name" id="name" value="<?php echo $user['name']; ?>" style="display:none;">
            </p>
            <p><strong>Character ID:</strong> <?php echo $user['characterId']; ?></p>
            <!-- Collar Number -->
            <p>
                <strong>Collar Number:</strong> 
                <span class="display-mode"><?php echo $user['collarNumber']; ?></span>
                <input type="text" class="edit-mode" name="collarNumber" id="collarNumber" value="<?php echo $user['collarNumber']; ?>" style="display:none;">
            </p>
            <!-- Rank -->
            <p>
                <strong>Rank:</strong> 
                <span class="display-mode"><?php echo $user['rank']; ?></span>
                <select class="edit-mode rank-select" name="rank" id="rank" style="display:none;">
                    <?php foreach (array_reverse($rankOrder, true) as $rank) : ?>
                        <?php if (array_search($rank, $rankOrder) <= $rankIndex) continue; ?>
                        <option value="<?php echo $rank; ?>" <?php echo $user['rank'] === $rank ? 'selected' : ''; ?>><?php echo $rank; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <!-- Unit -->
            <p>
                <strong>Unit:</strong> 
                <span class="display-mode"><?php echo $user['unit']; ?></span>
                <select class="edit-mode unit-select" name="unit" id="unit" style="display:none;">
                    <?php foreach ($units as $unit) : ?>
                        <option value="<?php echo $unit; ?>" <?php echo $user['unit'] === $unit ? 'selected' : ''; ?>><?php echo $unit; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <!-- Training Data -->
            <div class="training-container">
                <!-- NPAS -->
                <div class="training-card">
                    <label>NPAS:</label>
                    <span id="npasDisplay" class="display-mode"><?php echo $user['sub_divisions_npas']; ?></span>
                    <select id="npasEdit" class="edit-mode" name="sub_divisions_npas" style="display:none;">
                        <option value="No" <?php echo $user['sub_divisions_npas'] == "No" ? "selected" : ""; ?>>No</option>
                        <option value="Basic" <?php echo $user['sub_divisions_npas'] == "Basic" ? "selected" : ""; ?>>Basic</option>
                        <option value="Advanced" <?php echo $user['sub_divisions_npas'] == "Advanced" ? "selected" : ""; ?>>Advanced</option>
                    </select>
                </div>
                <!-- DSU -->
                <div class="training-card">
                    <label>DSU:</label>
                    <span id="dsuDisplay" class="display-mode"><?php echo $user['sub_divisions_dsu']; ?></span>
                    <select id="dsuEdit" class="edit-mode" name="sub_divisions_dsu" style="display:none;">
                        <option value="No" <?php echo $user['sub_divisions_dsu'] == "No" ? "selected" : ""; ?>>No</option>
                        <option value="Basic" <?php echo $user['sub_divisions_dsu'] == "Basic" ? "selected" : ""; ?>>Basic</option>
                        <option value="Advanced" <?php echo $user['sub_divisions_dsu'] == "Advanced" ? "selected" : ""; ?>>Advanced</option>
                    </select>
                </div>
                <!-- MPO -->
                <div class="training-card">
                    <label>MPO:</label>
                    <span id="mpoDisplay" class="display-mode"><?php echo $user['sub_divisions_mpo']; ?></span>
                    <select id="mpoEdit" class="edit-mode" name="sub_divisions_mpo" style="display:none;">
                        <option value="No" <?php echo $user['sub_divisions_mpo'] == "No" ? "selected" : ""; ?>>No</option>
                        <option value="Yes" <?php echo $user['sub_divisions_mpo'] == "Yes" ? "selected" : ""; ?>>Yes</option>
                    </select>
                </div>
                <!-- Driver Level -->
                <div class="training-card">
                    <label>Driver Level:</label>
                    <span id="driverDisplay" class="display-mode"><?php echo $user['driver_trainings_driving']; ?></span>
                    <select id="driverEdit" class="edit-mode" name="driver_trainings_driving" style="display:none;">
                        <option value="Standard" <?php echo $user['driver_trainings_driving'] == "Standard" ? "selected" : ""; ?>>Standard</option>
                        <option value="Advanced" <?php echo $user['driver_trainings_driving'] == "Advanced" ? "selected" : ""; ?>>Advanced</option>
                    </select>
                </div>
                <!-- TacAD -->
                <div class="training-card">
                    <label>TacAD:</label>
                    <span id="tacadDisplay" class="display-mode"><?php echo $user['driver_trainings_tacad']; ?></span>
                    <select id="tacadEdit" class="edit-mode" name="driver_trainings_tacad" style="display:none;">
                        <option value="No" <?php echo $user['driver_trainings_tacad'] == "No" ? "selected" : ""; ?>>No</option>
                        <option value="Yes" <?php echo $user['driver_trainings_tacad'] == "Yes" ? "selected" : ""; ?>>Yes</option>
                    </select>
                </div>
                <!-- PSU -->
                <div class="training-card">
                    <label>PSU:</label>
                    <span id="psuDisplay" class="display-mode"><?php echo $user['use_of_force_trainings_psu']; ?></span>
                    <select id="psuEdit" class="edit-mode" name="use_of_force_trainings_psu" style="display:none;">
                        <option value="Level 1" <?php echo $user['use_of_force_trainings_psu'] == "Level 1" ? "selected" : ""; ?>>Level 1</option>
                        <option value="Level 2" <?php echo $user['use_of_force_trainings_psu'] == "Level 2" ? "selected" : ""; ?>>Level 2</option>
                        <option value="Level 3" <?php echo $user['use_of_force_trainings_psu'] == "Level 3" ? "selected" : ""; ?>>Level 3</option>
                        <option value="Level 4" <?php echo $user['use_of_force_trainings_psu'] == "Level 4" ? "selected" : ""; ?>>Level 4</option>
                </select>
                </div>
                <!-- Firearms -->
                <div class="training-card">
                    <label>Firearms Training:</label>
                    <span id="firearmsDisplay" class="display-mode"><?php echo $user['use_of_force_trainings_firearms']; ?></span>
                    <select id="firearmsEdit" class="edit-mode" name="use_of_force_trainings_firearms" style="display:none;">
                        <option value="OST" <?php echo $user['use_of_force_trainings_firearms'] == "OST" ? "selected" : ""; ?>>OST</option>
                        <option value="Taser" <?php echo $user['use_of_force_trainings_firearms'] == "Taser" ? "selected" : ""; ?>>Taser</option>
                        <option value="FTO" <?php echo $user['use_of_force_trainings_firearms'] == "FTO" ? "selected" : ""; ?>>FTO</option>
                        <option value="T/AFO" <?php echo $user['use_of_force_trainings_firearms'] == "T/AFO" ? "selected" : ""; ?>>T/AFO</option>
                        <option value="AFO" <?php echo $user['use_of_force_trainings_firearms'] == "AFO" ? "selected" : ""; ?>>AFO</option>
                        <option value="T/SFO" <?php echo $user['use_of_force_trainings_firearms'] == "T/SFO" ? "selected" : ""; ?>>T/SFO</option>
                        <option value="SFO" <?php echo $user['use_of_force_trainings_firearms'] == "SFO" ? "selected" : ""; ?>>SFO</option>
                        <option value="T/CT-SFO" <?php echo $user['use_of_force_trainings_firearms'] == "T/CT-SFO" ? "selected" : ""; ?>>T/CT-SFO</option>
                        <option value="CT-SFO" <?php echo $user['use_of_force_trainings_firearms'] == "CT-SFO" ? "selected" : ""; ?>>CT-SFO</option>
                    </select>
                </div>
                <!-- Forensics -->
                <div class="training-card">
                    <label>Forensics:</label>
                    <span id="forensicsDisplay" class="display-mode"><?php echo $user['additional_trainings_forensics']; ?></span>
                    <select id="forensicsEdit" class="edit-mode" name="additional_trainings_forensics" style="display:none;">
                        <option value="No" <?php echo $user['additional_trainings_forensics'] == "No" ? "selected" : ""; ?>>No</option>
                        <option value="Yes" <?php echo $user['additional_trainings_forensics'] == "Yes" ? "selected" : ""; ?>>Yes</option>
                    </select>
                </div>
                <!-- FIM -->
                <div class="training-card">
                    <label>FIM:</label>
                    <span id="fimDisplay" class="display-mode"><?php echo $user['additional_trainings_fim']; ?></span>
                    <select id="fimEdit" class="edit-mode" name="additional_trainings_fim" style="display:none;">
                        <option value="No" <?php echo $user['additional_trainings_fim'] == "No" ? "selected" : ""; ?>>No</option>
                        <option value="Yes" <?php echo $user['additional_trainings_fim'] == "Yes" ? "selected" : ""; ?>>Yes</option>
                    </select>
                </div>
                <!-- Training Officer -->
                <div class="training-card">
                    <label>Training Officer:</label>
                    <span id="toDisplay" class="display-mode"><?php echo $user['additional_trainings_training_officer']; ?></span>
                    <select id="toEdit" class="edit-mode" name="additional_trainings_training_officer" style="display:none;">
                        <option value="No" <?php echo $user['additional_trainings_training_officer'] == "No" ? "selected" : ""; ?>>No</option>
                        <option value="Yes" <?php echo $user['additional_trainings_training_officer'] == "Yes" ? "selected" : ""; ?>>Yes</option>
                    </select>
                </div>
                <!-- Medical Training -->
                <div class="training-card">
                    <label>TacAD:</label>
                    <span id="medicalDisplay" class="display-mode"><?php echo $user['additional_trainings_medical']; ?></span>
                    <select id="medicalEdit" class="edit-mode" name="additional_trainings_medical" style="display:none;">
                        <option value="Basic" <?php echo $user['additional_trainings_medical'] == "Basic" ? "selected" : ""; ?>>Basic</option>
                        <option value="Advanced" <?php echo $user['additional_trainings_medical'] == "Advanced" ? "selected" : ""; ?>>Advanced</option>
                    </select>
                </div>
                <!-- Save Button -->
                <input id="saveButton" type="submit" value="Save" style="display:none;">
            </form>
    </div>
</body>

<script>
    window.onload = function() {
        var displayItems = document.getElementsByClassName('display-mode');
        var editItems = document.getElementsByClassName('edit-mode');
        var saveButton = document.getElementById('saveButton');

        for (var i = 0; i < displayItems.length; i++) {
            displayItems[i].style.display = 'inline-block';
            editItems[i].style.display = 'none';
        }

        saveButton.style.display = 'none';
    }
    function toggleEditMode() {
    var displayItems = document.getElementsByClassName('display-mode');
    var editItems = document.getElementsByClassName('edit-mode');
    var saveButton = document.getElementById('saveButton');
    var editButton = document.getElementById('editButton');
    var editForm = document.getElementById('editForm');

    // If currently in display mode
    if (displayItems[0].style.display !== 'none') {
        for (var i = 0; i < displayItems.length; i++) {
            displayItems[i].style.display = 'none';
            editItems[i].style.display = 'inline-block';
        }

        saveButton.style.display = 'inline-block';
        editForm.style.display = 'block';
        editButton.disabled = true; // Disable the edit button
    } else {
        for (var i = 0; i < displayItems.length; i++) {
            displayItems[i].style.display = 'inline-block';
            editItems[i].style.display = 'none';
        }

        saveButton.style.display = 'none';
        editForm.style.display = 'none';
        editButton.disabled = false; // Re-enable the edit button
    }
}
</script>
</body>
</html>