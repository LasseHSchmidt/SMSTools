<?php
    require_once 'init.php';

    // Check if the request is a POST and the required ID is present
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiverid'])) 
    {
        $receiverid = $_POST['receiverid'];
        // Retrieve listid and phone from POST as they were submitted with the form
        $current_listid = $_POST['listid'] ?? $listid;
        $current_phone  = $_POST['phone'] ?? $fromphone;

        $con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);

        if (!$con) {
            die("Connection error: " . mysqli_connect_error());
        }

        // Secure deletion using a Prepared Statement
        $stmt = $con->prepare("DELETE FROM $TReceivers WHERE ReceiverId = ?");
        $stmt->bind_param("i", $receiverid);

        if ($stmt->execute()) {
            $stmt->close();
            mysqli_close($con);

            // Redirect back to the receiver list
            $to = "index.php?cmd=receivers&listid=$current_listid&phone=$current_phone";
            header('Location: ' . $to);
            exit();
        } else {
            die("Error deleting record: " . $stmt->error);
        }
    } else {
        // Redirect if accessed directly without a POST request
        header('Location: index.php');
        exit();
    }
?>