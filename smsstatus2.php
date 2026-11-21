<?php
require_once 'init.php';

// Check if listid is set; otherwise redirect to login
if (!$listid) {
    header('Location: index.php?cmd=login');
    exit();
}

// Connect to the database
$mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
if (!$mysqli) {
    die("Could not connect to database: " . mysqli_connect_error());
}
mysqli_set_charset($mysqli, "utf8mb4");

// Fetch SMS log records using prepared statements
if ((int)$listid === 4) {
    $stmt = $mysqli->prepare("SELECT CellPhone, Message, Status, Date FROM $TSendSMS ORDER BY SMSId DESC LIMIT 20");
} else {
    $stmt = $mysqli->prepare("SELECT CellPhone, Message, Status, Date FROM $TSendSMS WHERE ListId = ? ORDER BY SMSId DESC LIMIT 200");
    $stmt->bind_param("i", $listid);
}
$stmt->execute();
$results = $stmt->get_result();
?>

<table class="modern-table">
    <thead>
        <tr>
            <th><?php echo htmlspecialchars(__('log_sent_to'), ENT_QUOTES, 'UTF-8'); ?></th>
            <th><?php echo htmlspecialchars(__('log_message'), ENT_QUOTES, 'UTF-8'); ?></th>
            <th><?php echo htmlspecialchars(__('log_status'), ENT_QUOTES, 'UTF-8'); ?></th>
            <th><?php echo htmlspecialchars(__('log_date'), ENT_QUOTES, 'UTF-8'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php while ($line = $results->fetch_assoc()): 
            $status = $line["Status"] ?: "Waiting";
        ?>
            <tr>
                <td><?php echo htmlspecialchars((string)$line["CellPhone"], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars((string)$line["Message"], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <span class="status-badge <?php echo htmlspecialchars(strtolower($status), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars((string)$line["Date"], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
$stmt->close();
mysqli_close($mysqli);
?>
