<?php
require_once 'init.php';

// 1. Logik: Håndter indsendelse af opdatering (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
    $con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
    mysqli_set_charset($con, "utf8mb4");

    if (!$con) {
        die("Forbindelsesfejl: " . mysqli_connect_error());
    }

    $receiverid = $_POST['receiverid'] ?? 0;
    $fullname   = $_POST['fullname'] ?? '';
    $shortname  = $_POST['shortname'] ?? '';
    $cellphone  = $_POST['cellphone'] ?? '';
    $phone      = !empty($_POST['phone']) ? $_POST['phone'] : 0;
    $email      = $_POST['email'] ?? '';
    $birthday   = !empty($_POST['birthday']) ? $_POST['birthday'] : "0000-00-00";

    // Prepared Statement for sikkerhed
    $sql = "UPDATE $TReceivers SET 
            FullName = ?, 
            ShortName = ?, 
            CellPhone = ?, 
            Phone = ?, 
            Email = ?, 
            BirthDay = ? 
            WHERE ReceiverId = ?";
            
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssssi", $fullname, $shortname, $cellphone, $phone, $email, $birthday, $receiverid);

    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($con);
        // Redirect tilbage til oversigten
        echo "<script>window.location.href='index.php?cmd=receivers&listid=$listid&phone=$fromphone';</script>";
        exit();
    } else {
        $error = "Fejl ved opdatering: " . $stmt->error;
        $stmt->close();
        mysqli_close($con);
    }
}

// 2. Logik: Hent eksisterende data til formularen
$receiverid = isset($_REQUEST['receiverid']) ? $_REQUEST['receiverid'] : 0;
$con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
mysqli_set_charset($con, "utf8mb4");

if (!$con) {
    die("Kunne ikke forbinde til databasen");
}

$stmt = $con->prepare("SELECT * FROM $TReceivers WHERE ReceiverId = ?");
$stmt->bind_param("i", $receiverid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("Modtager blev ikke fundet.");
}

$fullname  = $row['FullName'];
$shortname = $row['ShortName'];
$cellphone = $row['CellPhone'];
$phone     = $row['Phone'];
$email     = $row['Email'];
$birthday  = ($row['BirthDay'] == "0000-00-00") ? "" : $row['BirthDay'];

$stmt->close();
mysqli_close($con);
?>

<div class="form-container">
    <h2>Opdater kontakt</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <input type="hidden" name="receiverid" value="<?php echo htmlspecialchars($receiverid); ?>" />
        <input type="hidden" name="listid" value="<?php echo htmlspecialchars($listid); ?>" />
        
        <div class="form-group">
            <label for="fullname">Fuldt navn</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required />
        </div>

        <div class="form-group">
            <label for="shortname">Fornavn (Kaldenavn)</label>
            <input type="text" id="shortname" name="shortname" value="<?php echo htmlspecialchars($shortname); ?>" />
        </div>

        <div class="form-group">
            <label for="cellphone">Mobilnummer</label>
            <input type="text" id="cellphone" name="cellphone" value="<?php echo htmlspecialchars($cellphone); ?>" required />
        </div>

        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>" />
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>" />
        <input type="hidden" name="birthday" value="<?php echo htmlspecialchars($birthday); ?>" />

        <button type="submit" name="ok" class="submit-btn">Gem ændringer</button>
    </form>
</div>