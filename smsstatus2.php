<?php
require_once 'init.php';

// Tjekker om listid er sat, ellers send til login
if (!$listid) {
    $to = 'index.php?cmd=login';
    header('Location: ' . $to);
    exit();
}

// Opretter forbindelse til databasen
$mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
mysqli_set_charset($mysqli, "utf8mb4");

if (!$mysqli) {
    die("Kunne ikke forbinde til databasen: " . mysqli_connect_error());
}

// Henter data (Sikkerhed: bruger prepared statement til listid)
if ($listid == 4) {
    $sqlquery = "SELECT * FROM $TSendSMS ORDER BY SMSId DESC LIMIT 0, 20";
    $results = mysqli_query($mysqli, $sqlquery) or die("Kunne ikke hente data");
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
            <th>Sendt til</th>
            <th>Besked</th>
            <th>Status</th>
            <th>Dato</th>
            
        </tr>
    </thead>
    <tbody>
        <?php while ($line = mysqli_fetch_array($results)): 
            $status = $line["Status"] ?: "Waiting";
        ?>
            <tr>
                <td><?php echo htmlspecialchars($line["CellPhone"]); ?></td>
                <td><?php echo htmlspecialchars($line["Message"]); ?></td>
                <td>
                    <span class="status-badge <?php echo strtolower($status); ?>">
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