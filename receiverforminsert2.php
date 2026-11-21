<?php
require_once 'init.php';

// 1. Logic: Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
    verify_csrf();

    $con = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
    if (!$con) {
        die("Connection error: " . mysqli_connect_error());
    }
    mysqli_set_charset($con, "utf8mb4");

    // Sanitize and prepare input data
    $fullname   = trim($_POST['fullname'] ?? '');
    $shortname  = trim($_POST['shortname'] ?? '');
    $cellphone  = trim($_POST['cellphone'] ?? '');
    $phone      = !empty($_POST['phone']) ? $_POST['phone'] : '0';
    $email      = trim($_POST['email'] ?? '');
    $birthday   = !empty($_POST['birthday']) ? $_POST['birthday'] : "0000-00-00";

    // Prepared statement to prevent SQL injection
    $query = "INSERT INTO $TReceivers (ListId, FullName, ShortName, CellPhone, Phone, Email, BirthDay) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("issssss", $listid, $fullname, $shortname, $cellphone, $phone, $email, $birthday);

    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($con);

        $to = "index.php?cmd=receivers&listid=$listid&phone=$fromphone";
        header('Location: ' . $to);
        exit();
    } else {
        $error = "Error inserting record: " . $stmt->error;
        $stmt->close();
        mysqli_close($con);
    }
}
?>

<div class="form-container">
    <h2><?php echo htmlspecialchars(__('insert_title'), ENT_QUOTES, 'UTF-8'); ?></h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="listid" value="<?php echo htmlspecialchars((string)$listid, ENT_QUOTES, 'UTF-8'); ?>" />
        
        <div class="form-group">
            <label for="fullname"><?php echo htmlspecialchars(__('label_fullname'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" id="fullname" name="fullname" placeholder="<?php echo htmlspecialchars(__('ph_fullname'), ENT_QUOTES, 'UTF-8'); ?>" required onblur="copyFirstName()" />
        </div>

        <div class="form-group">
            <label for="shortname"><?php echo htmlspecialchars(__('label_shortname'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" id="shortname" name="shortname" placeholder="<?php echo htmlspecialchars(__('ph_shortname'), ENT_QUOTES, 'UTF-8'); ?>" />
        </div>

        <div class="form-group">
            <label for="cellphone"><?php echo htmlspecialchars(__('label_cellphone'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" 
                    id="cellphone" 
                    name="cellphone" 
                    placeholder="88888888" 
                    required 
                    maxlength="8" 
                    pattern="[0-9]{8}"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
        </div>

        <input type="hidden" name="phone" />
        <input type="hidden" name="email" />
        <input type="hidden" name="birthday" />

        <button type="submit" name="ok" class="submit-btn"><?php echo htmlspecialchars(__('btn_save_contact'), ENT_QUOTES, 'UTF-8'); ?></button>
    </form>
</div>

<script>
function copyFirstName() {
    var fullNameInput = document.getElementById('fullname');
    var shortNameInput = document.getElementById('shortname');

    if (shortNameInput.value.trim() === "") {
        var name = fullNameInput.value.trim();
        if (name !== "") {
            var firstName = name.split(' ')[0];
            shortNameInput.value = firstName;
        }
    }
}
</script>
