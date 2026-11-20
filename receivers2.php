<?php
require_once 'init.php';

if (!$listid) {
    echo "<script>window.location.href='index.php?cmd=login';</script>";
    exit;
}

$orderby = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : "Fullname";
$mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName) or die("Unable to connect to database");
mysqli_set_charset($mysqli, "utf8mb4");

$sqlquery = "SELECT * FROM $TReceivers WHERE ListId = '$listid' ORDER BY $orderby";
$results = mysqli_query($mysqli, $sqlquery) or die("Unable to query");
?>

<div class="table-container">
    <table class="modern-table">
        <thead>
            <tr>
                <th>Navn</th>
                <th>Fornavn</th>
                <th>Mobil</th>
                <th colspan="2" style="text-align: center;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($line = mysqli_fetch_array($results)) {
                $receiverid = $line["ReceiverId"];
                $fullname   = $line["FullName"];
                $shortname  = $line["ShortName"];
                $cellphone  = ($line["CellPhone"] == "0") ? "" : $line["CellPhone"];
                ?>
                <tr>
                    <td class="name-cell"><?php echo $fullname; ?></td>
                    <td><?php echo $shortname; ?></td>
                    <td class="mono"><?php echo $cellphone; ?></td>
                    <td class="action-cell">
                        <a href="index.php?cmd=receiverformupdate&receiverid=<?php echo $receiverid; ?>&listid=<?php echo $listid; ?>&phone=<?php echo $fromphone; ?>" class="btn-edit">Rediger</a>
                    </td>
                    <td class="action-cell">
                       <form action="receiverdelete2.php" method="post" style="display:inline;">
                        <input type="hidden" name="receiverid" value="<?php echo $receiverid; ?>">
                        <input type="hidden" name="listid" value="<?php echo $listid; ?>">
                        <input type="hidden" name="phone" value="<?php echo $fromphone; ?>">
                        
                        <button type="submit" 
                                class="btn-delete" 
                                onclick="return confirm('Er du sikker på, at du vil slette denne modtager?')"
                                style="cursor:pointer; border:none; background:none; padding:0; font:inherit;">
                            Slet
                        </button>
                    </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
mysqli_free_result($results);
mysqli_close($mysqli);
?>