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
    <link rel="stylesheet" href="stylesheet.css">
    <title>Main Page</title>
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
    <button class="button" onclick="location.href='logout.php';">Logout</button>
</body>
</html>