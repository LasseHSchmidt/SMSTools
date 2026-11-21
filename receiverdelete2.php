<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiverid'])) {
    if (!$isLoggedIn) {
        header('Location: index.php?cmd=login');
        exit();
    }

    verify_csrf();

    $receiverid     = (int)$_POST['receiverid'];
    $current_listid = (int)$listid;
    $current_phone  = $fromphone;

    $con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
    if (!$con) {
        die("Connection error: " . mysqli_connect_error());
    }
    mysqli_set_charset($con, "utf8mb4");

    // Scope deletion to both ReceiverId and the authenticated session's ListId
    $stmt = $con->prepare("DELETE FROM $TReceivers WHERE ReceiverId = ? AND ListId = ?");
    $stmt->bind_param("ii", $receiverid, $current_listid);

    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($con);

        header("Location: index.php?cmd=receivers&listid=$current_listid&phone=$current_phone");
        exit();
    } else {
        die("Error deleting record: " . $stmt->error);
    }
} else {
    header('Location: index.php');
    exit();
}
?>
