<?php
    require_once 'init.php';

    // Vi tjekker om anmodningen er en POST og om de nødvendige ID'er er med
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiverid'])) 
    {
        $receiverid = $_POST['receiverid'];
        // listid og phone hentes typisk via POST her, da de blev sendt med fra formen
        $current_listid = $_POST['listid'] ?? $listid;
        $current_phone  = $_POST['phone'] ?? $fromphone;

        $con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);

        if (!$con) {
            die("Forbindelsesfejl: " . mysqli_connect_error());
        }

        // Sikker sletning med Prepared Statement
        $stmt = $con->prepare("DELETE FROM $TReceivers WHERE ReceiverId = ?");
        $stmt->bind_param("i", $receiverid);

        if ($stmt->execute()) {
            $stmt->close();
            mysqli_close($con);

            // Redirect tilbage til listen
            $to = "index.php?cmd=receivers&listid=$current_listid&phone=$current_phone";
            header('Location: ' . $to);
            exit();
        } else {
            die("Fejl ved sletning: " . $stmt->error);
        }
    } else {
        // Hvis nogen prøver at tilgå filen direkte uden POST
        header('Location: index.php');
        exit();
    }
?>