<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['characterId'])) {
    header('Location: index.html');
    exit();
}

$welcomeMessage = "Welcome, " . $_SESSION['rank'] . " " . $_SESSION['name'];

// Include the database connection
include 'config.php';

// Fetch users from the database and sort them by rank
$sql = "SELECT characterId, rank, collarNumber, name, unit FROM users ORDER BY FIELD(unit, 'Police Command', 'Frontline', 'Response', 'Recruitment & Development', 'Standards', 'Tactical Operations', 'Roads Policing Unit', 'Firearms', 'CID', 'Policing Operations', 'NPAS', 'DSU', 'MPO', 'PSU', 'Archive'), FIELD(rank, 'Chief Constable', 'Deputy Chief Constable', 'Assistant Chief Constable', 'Chief Superintendent', 'Superintendent', 'Chief Inspector', 'Inspector', 'Sergeant', 'Constable', 'Probationary Constable')";
$result = $conn->query($sql);
$users = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();

// Ranks which can access the register page
$accessibleRanks = array(
    'Chief Constable',
    'Deputy Chief Constable',
    'Assistant Chief Constable',
    'Chief Superintendent',
    'Superintendent',
    'Chief Inspector',
    'Inspector',
    'Sergeant'
);

// Check if the user's rank is in the accessible ranks array
$isAccessibleRank = in_array($_SESSION['rank'], $accessibleRanks);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Main Page</title>
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
            width: 80%;
        }
        th, td {
            padding: 10px;
            border: 1px solid #fff;
        }
        th {
            background-color: #073763; /* dark grey */
        }
        tr:first-child {
            background-color: #333; /* darkest grey */
        }
        .rank-epaulette {
            max-width: 30px !important;
            max-height: 30px !important;
            width: auto;
            height: auto;
        }
        /* Additional style for the table cell containing the image */
        .rank-epaulette-cell {
            width: 30px;
            height: 30px;
            padding: 0;
            text-align: center;
        }
        a {
            text-decoration: none; /* Remove underline from hyperlinks */
            color: inherit; /* Inherit color from parent element (in this case, the table cells) */
        }
        .logout-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #555; /* dark grey */
            color: #fff; /* white */
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
        tr:nth-child(even) {
            background-color: #444; /* darker grey */
        }
        td {
            color: #fff; /* white */
        }
        tr[data-rank="Chief Constable"] td:nth-child(4){
            background-color: #B45F06;
        }
        tr[data-rank="Deputy Chief Constable"] td:nth-child(4){
            background-color: #B45F06;
        }
        tr[data-rank="Assistant Chief Constable"] td:nth-child(4){
            background-color: #B45F06;
        }
        tr[data-rank="Chief Superintendent"] td:nth-child(4) {
            background-color: #B45F06; /* Gold for Chief Superintendent+ */
        }
        tr[data-rank="Superintendent"] td:nth-child(4) {
            background-color: #CC0000; /* Red for Superintendent */
        }
        tr[data-rank="Chief Inspector"] td:nth-child(4) {
            background-color: #741B47; /* Dark Magenta for Chief Inspector */
        }
        tr[data-rank="Inspector"] td:nth-child(4) {
            background-color: #C27BA0; /* Light Magenta for Inspector */
        }
        tr[data-rank="Sergeant"] td:nth-child(4) {
            background-color: #134F5C; /* Dark Cyan for Sergeant */
        }
        tr[data-rank="Constable"] td:nth-child(4) {
            background-color: #85200C; /* Dark Red Berry for Constable */
        }
        tr[data-rank="Probationary Constable"] td:nth-child(4) {
            background-color: #274E13; /* Dark Green for Probie */
        }
    </style>
</head>
<body>
    <h1><?php echo $welcomeMessage; ?></h1>
    <?php if ($isAccessibleRank) { ?>
        <button class="button" onclick="location.href='register.php';">Register New Officer</button>
        <button class="button" onclick="location.href='whitelist.php';">Whitelisting Requests</button>
        <button class="button" onclick="location.href='audit.php';">Audit Log</button>
    <?php } ?>
    <h2>Officers:</h2>
    <table>
        <tr>
            <th></th>
            <th>Collar Number</th>
            <th>Name</th>
            <th>Rank</th>
            <th>Character ID</th>
            <th>Unit</th>
        </tr>
        <?php
        $unitGroups = array(
            'Police Command' => array(),
            'Frontline' => array(),
            'Standards' => array(),
            'Tactical Operations' => array(),
            'CID' => array(),
            'Policing Operations' => array()
        );

        $hasResponseDisplayed = false;
        $hasRecruitmentDisplayed = false;
        $hasRoadsPolicingDisplayed = false;
        $hasFirearmsDisplayed = false;
        $hasNPASDisplayed = false;
        $hasDSUDisplayed = false;
        $hasMPODisplayed = false;
        $hasPSUDisplayed = false;

        // Group users based on the specified order of units
        foreach ($users as $user) {
            $unit = $user['unit'];
            if ($unit === 'Response') {
                $unit = 'Frontline'; // Set Response to Frontline for grouping
                $hasResponseDisplayed = true;
            } elseif ($unit === 'Recruitment & Development') {
                $unit = 'Frontline'; // Set Recruitment & Development to Frontline for grouping
                $hasRecruitmentDisplayed = true;
            } elseif ($unit === 'Roads Policing Unit') {
                $unit = 'Tactical Operations'; // Set Roads Policing Unit to Tactical Operations for grouping
                $hasRoadsPolicingDisplayed = true;
            } elseif ($unit === 'Firearms') {
                $unit = 'Tactical Operations'; // Set Firearms to Tactical Operations for grouping
                $hasFirearmsDisplayed = true;
            } elseif ($unit === 'NPAS') {
                $unit = 'Policing Operations'; // Set NPAS to Policing Operations for grouping
                $hasNPASDisplayed = true;
            } elseif ($unit === 'DSU') {
                $unit = 'Policing Operations'; // Set DSU to Policing Operations for grouping
                $hasDSUDisplayed = true;
            } elseif ($unit === 'MPO') {
                $unit = 'Policing Operations'; // Set MPO to Policing Operations for grouping
                $hasMPODisplayed = true;
            } elseif ($unit === 'PSU') {
                $unit = 'Policing Operations'; // Set PSU to Policing Operations for grouping
                $hasPSUDisplayed = true;
            }
            if (isset($unitGroups[$unit])) {
                $unitGroups[$unit][] = $user;
            } else {
                // If the unit is not found in the specified order, add it as an individual group
                $unitGroups[$unit] = array($user);
            }
        }

        foreach ($unitGroups as $unit => $groupUsers) {
            echo '<tr><th colspan="6" style="background-color: #073763;">' . $unit . '</th></tr>';
            if (count($groupUsers) > 0) {
                foreach ($groupUsers as $user) {
                    ?>
                    <tr data-rank="<?php echo $user['rank']; ?>">
                        <td class="rank-epaulette-cell">
                            <a href="user_profile.php?characterId=<?php echo urlencode($user['characterId']); ?>">
                                <img class="rank-epaulette" src="images/epaulettes/<?php echo strtolower(str_replace(' ', '_', $user['rank'])); ?>.png" alt="<?php echo $user['rank']; ?>">
                            </a>
                        </td>
                        <td><?php echo $user['collarNumber']; ?></td>
                        <td>
                            <a href="user_profile.php?characterId=<?php echo urlencode($user['characterId']); ?>">
                                <?php echo $user['name']; ?>
                            </a>
                        </td>
                        <td><?php echo $user['rank']; ?></td>
                        <td><?php echo $user['characterId']; ?></td>
                        <td><?php echo $user['unit']; ?></td>
                    </tr>
                    <?php
                }
            } else {
                // If no users were found for the current group, display an empty row
                echo '<tr><td colspan="6" style="text-align: center; color: #fff;">No members in this group</td></tr>';
            }
        }
        ?>
    </table>
    <button class="logout-button" onclick="location.href='logout.php';">Logout</button>
</body>
</html>