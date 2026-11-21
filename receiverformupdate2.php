<?php
require_once 'init.php';

$con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
if (!$con) {
    die("Connection error: " . mysqli_connect_error());
}
mysqli_set_charset($con, "utf8mb4");

// 1. Logic: Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
    verify_csrf();

    $receiverid = (int)($_POST['receiverid'] ?? 0);
    $fullname   = trim($_POST['fullname'] ?? '');
    $shortname  = trim($_POST['shortname'] ?? '');
    $cellphone  = trim($_POST['cellphone'] ?? '');
    $phone      = !empty($_POST['phone']) ? $_POST['phone'] : '0';
    $email      = trim($_POST['email'] ?? '');
    $birthday   = !empty($_POST['birthday']) ? $_POST['birthday'] : "0000-00-00";

    // Scope update to both ReceiverId and session $listid
    $stmt = $con->prepare("UPDATE $TReceivers SET FullName = ?, ShortName = ?, CellPhone = ?, Phone = ?, Email = ?, BirthDay = ? WHERE ReceiverId = ? AND ListId = ?");
    $stmt->bind_param("ssssssii", $fullname, $shortname, $cellphone, $phone, $email, $birthday, $receiverid, $listid);

    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($con);

        header("Location: index.php?cmd=receivers&listid=$listid&phone=$fromphone");
        exit();
    } else {
        $error = "Error updating record: " . $stmt->error;
        $stmt->close();
    }
}

// 2. Fetch existing contact details scoped to session $listid
$receiverid = (int)($_REQUEST['receiverid'] ?? 0);
$stmt = $con->prepare("SELECT ReceiverId, FullName, ShortName, CellPhone, Phone, Email, BirthDay FROM $TReceivers WHERE ReceiverId = ? AND ListId = ? LIMIT 1");
$stmt->bind_param("ii", $receiverid, $listid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
mysqli_close($con);

if (!$row) {
    echo "<div class='error-msg'>" . htmlspecialchars(__('err_not_found'), ENT_QUOTES, 'UTF-8') . "</div>";
    return;
}

$fullname  = $row['FullName'];
$shortname = $row['ShortName'];
$cellphone = $row['CellPhone'];
$phone     = $row['Phone'];
$email     = $row['Email'];
$birthday  = $row['BirthDay'];
?>

<div class="form-container">
    <h2><?php echo htmlspecialchars(__('update_title'), ENT_QUOTES, 'UTF-8'); ?></h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="receiverid" value="<?php echo htmlspecialchars((string)$receiverid, ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="hidden" name="listid" value="<?php echo htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8'); ?>" />
        
        <div class="form-group">
            <label for="fullname"><?php echo htmlspecialchars(__('label_fullname'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars((string)$fullname, ENT_QUOTES, 'UTF-8'); ?>" required />
        </div>

        <div class="form-group">
            <label for="shortname"><?php echo htmlspecialchars(__('label_shortname'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" id="shortname" name="shortname" value="<?php echo htmlspecialchars((string)$shortname, ENT_QUOTES, 'UTF-8'); ?>" />
        </div>

        <div class="form-group">
            <label for="cellphone"><?php echo htmlspecialchars(__('col_mobile'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" id="cellphone" name="cellphone" value="<?php echo htmlspecialchars((string)$cellphone, ENT_QUOTES, 'UTF-8'); ?>" required />
        </div>

        <input type="hidden" name="phone" value="<?php echo htmlspecialchars((string)$phone, ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="hidden" name="email" value="<?php echo htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="hidden" name="birthday" value="<?php echo htmlspecialchars((string)$birthday, ENT_QUOTES, 'UTF-8'); ?>" />

        <button type="submit" name="ok" class="submit-btn"><?php echo htmlspecialchars(__('btn_save_changes'), ENT_QUOTES, 'UTF-8'); ?></button>
    </form>
</div>
