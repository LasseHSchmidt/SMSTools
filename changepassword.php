<?php
require_once 'init.php';

if (!$isLoggedIn || !$listid) {
    header('Location: index.php?cmd=login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pw'])) {
    verify_csrf();

    $newPw     = $_POST['new_password'] ?? '';
    $confirmPw = $_POST['confirm_password'] ?? '';

    if (strlen($newPw) < 8) {
        $error = __('pw_err_short');
    } elseif ($newPw !== $confirmPw) {
        $error = __('pw_err_mismatch');
    } else {
        $mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
        if (!$mysqli) {
            die("Connection failed: " . mysqli_connect_error());
        }
        mysqli_set_charset($mysqli, "utf8mb4");

        // Verify the new password is not the same as the current default password
        $stmt = $mysqli->prepare("SELECT Password FROM $TListName WHERE ListId = ? LIMIT 1");
        $stmt->bind_param("i", $listid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($newPw, $row['Password'])) {
            $error = __('pw_err_same');
            mysqli_close($mysqli);
        } else {
            $hashedPw = password_hash($newPw, PASSWORD_DEFAULT);
            $upd = $mysqli->prepare("UPDATE $TListName SET Password = ?, MustChangePassword = 0 WHERE ListId = ?");
            $upd->bind_param("si", $hashedPw, $listid);
            $upd->execute();
            $upd->close();
            mysqli_close($mysqli);

            $_SESSION['must_change_password'] = false;
            $_SESSION['csrf_token']           = bin2hex(random_bytes(32));

            header("Location: index.php?cmd=receivers&listid=$listid&phone=$fromphone");
            exit();
        }
    }
}
?>

<div class="login-container">
    <h2><?php echo htmlspecialchars(__('pw_change_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
    <p style="margin-bottom: 20px; color: var(--text-muted);"><?php echo htmlspecialchars(__('pw_change_notice'), ENT_QUOTES, 'UTF-8'); ?></p>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?cmd=changepassword">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="new_password"><?php echo htmlspecialchars(__('pw_new'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="password" name="new_password" id="new_password" autocomplete="new-password" minlength="8" required autofocus />
        </div>
        <div class="form-group">
            <label for="confirm_password"><?php echo htmlspecialchars(__('pw_confirm'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="password" name="confirm_password" id="confirm_password" autocomplete="new-password" minlength="8" required />
        </div>

        <button type="submit" name="change_pw" class="login-btn"><?php echo htmlspecialchars(__('pw_btn_save'), ENT_QUOTES, 'UTF-8'); ?></button>
    </form>
</div>
