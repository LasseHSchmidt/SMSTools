<?php
require_once 'init.php';

// Redirect to login if no list ID is present in the session
if (!$listid) {
    echo "<script>window.location.href='index.php?cmd=login';</script>";
    exit;
}

// Whitelist allowed sort columns to prevent SQL injection
$allowed_columns = ['FullName', 'ShortName', 'CellPhone'];
$orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], $allowed_columns, true)) 
    ? $_REQUEST['orderby'] 
    : 'FullName';

$mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
if (!$mysqli) {
    die("Unable to connect to database: " . mysqli_connect_error());
}
mysqli_set_charset($mysqli, "utf8mb4");

// Fetch contacts for the active list using a prepared statement
$stmt = $mysqli->prepare("SELECT ReceiverId, FullName, ShortName, CellPhone FROM $TReceivers WHERE ListId = ? ORDER BY $orderby");
$stmt->bind_param("i", $listid);
$stmt->execute();
$results = $stmt->get_result();
?>

<div class="table-container">
    <table class="modern-table">
        <thead>
            <tr>
                <th><?php echo htmlspecialchars(__('col_name'), ENT_QUOTES, 'UTF-8'); ?></th>
                <th><?php echo htmlspecialchars(__('col_firstname'), ENT_QUOTES, 'UTF-8'); ?></th>
                <th><?php echo htmlspecialchars(__('col_mobile'), ENT_QUOTES, 'UTF-8'); ?></th>
                <th colspan="2" style="text-align: center;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($line = $results->fetch_assoc()) {
                $receiverid = $line["ReceiverId"];
                $fullname   = $line["FullName"];
                $shortname  = $line["ShortName"];
                $cellphone  = ($line["CellPhone"] == "0") ? "" : $line["CellPhone"];
                ?>
                <tr>
                    <td class="name-cell"><?php echo htmlspecialchars((string)$fullname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$shortname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="mono"><?php echo htmlspecialchars((string)$cellphone, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="action-cell">
                        <a href="index.php?cmd=receiverformupdate&receiverid=<?php echo htmlspecialchars((string)$receiverid, ENT_QUOTES, 'UTF-8'); ?>&listid=<?php echo htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8'); ?>&phone=<?php echo htmlspecialchars((string)$fromphone, ENT_QUOTES, 'UTF-8'); ?>" class="btn-edit">
                            <?php echo htmlspecialchars(__('btn_edit'), ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </td>
                    <td class="action-cell">
                        <form action="receiverdelete2.php" method="post" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="receiverid" value="<?php echo htmlspecialchars((string)$receiverid, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="listid" value="<?php echo htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="phone" value="<?php echo htmlspecialchars((string)$fromphone, ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <button type="submit" 
                                    class="btn-delete" 
                                    onclick="return confirm(<?php echo htmlspecialchars(json_encode(__('confirm_delete')), ENT_QUOTES, 'UTF-8'); ?>)"
                                    style="cursor:pointer; border:none; background:none; padding:0; font:inherit;">
                                <?php echo htmlspecialchars(__('btn_delete'), ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
$stmt->close();
mysqli_close($mysqli);
?>
