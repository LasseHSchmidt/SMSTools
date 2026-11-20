<?php
require_once 'init.php';

// Check if listid is set; otherwise redirect to login
if (!$listid) {
    $to = 'index.php?cmd=login';
    header('Location: ' . $to);
    exit();
}

// Connect to the database
$mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
mysqli_set_charset($mysqli, "utf8mb4");

if (!$mysqli) {
    die("Could not connect to database: " . mysqli_connect_error());
}

// Fetch SMS log records (using a prepared statement for listid)
if ($listid == 4) {
    $sqlquery = "SELECT * FROM $TSendSMS ORDER BY SMSId DESC LIMIT 0, 20";
    $results = mysqli_query($mysqli, $sqlquery) or die("Could not fetch data");
} else {
    $stmt = $mysqli->prepare("SELECT * FROM $TSendSMS WHERE ListId = ? ORDER BY SMSId DESC LIMIT 0, 200");
    $stmt->bind_param("i", $listid);
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<table class="modern-table">
    <thead>
        <tr>
            <th><?php echo htmlspecialchars(__('log_sent_to')); ?></th>
            <th><?php echo htmlspecialchars(__('log_message')); ?></th>
            <th><?php echo htmlspecialchars(__('log_status')); ?></th>
            <th><?php echo htmlspecialchars(__('log_date')); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php while ($line = $results->fetch_assoc()): 
            $status = $line["Status"] ?: "Waiting";
        ?>
            <tr>
                <td><?php echo htmlspecialchars($line["CellPhone"]); ?></td>
                <td><?php echo htmlspecialchars($line["Message"]); ?></td>
                <td>
                    <span class="status-badge <?php echo htmlspecialchars(strtolower($status)); ?>">
                        <?php echo htmlspecialchars($status); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($line["Date"]); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
if (isset($stmt)) {
    $stmt->close();
}
mysqli_free_result($results);
mysqli_close($mysqli);
?>