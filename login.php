<?php
// 1. Logic: Handle login validation (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    require_once 'init.php';
    verify_csrf();

    // Sanitize username input
    $formuser = strtolower(trim(substr($_POST['user'] ?? '', 0, 50)));
    $formpw   = $_POST['password'] ?? '';

    $mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
    if (!$mysqli) {
        die("Connection failed: " . mysqli_connect_error());
    }
    mysqli_set_charset($mysqli, "utf8mb4");

    // Prepared statement to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT Username, Password, ListId, CellPhone, MustChangePassword FROM $TListName WHERE Username = ? LIMIT 1");
    $stmt->bind_param("s", $formuser);
    $stmt->execute();
    $result = $stmt->get_result();
    $line = $result->fetch_assoc();

    if ($line) {
        $valuser    = strtolower($line["Username"]);
        $valpw      = $line["Password"];
        $vallistid  = $line["ListId"];
        $fromphone  = $line["CellPhone"];
        $mustChange = (int)($line["MustChangePassword"] ?? 0) === 1;

        // Verify username and hashed password
        if ($valuser === $formuser && password_verify($formpw, $valpw)) {
            session_regenerate_id(true);
            $_SESSION['listid']               = $vallistid;
            $_SESSION['phone']                = $fromphone;
            $_SESSION['logged_in']            = true;
            $_SESSION['must_change_password'] = $mustChange;
            $_SESSION['csrf_token']           = bin2hex(random_bytes(32));

            $stmt->close();
            mysqli_close($mysqli);

            if ($mustChange) {
                header("Location: index.php?cmd=changepassword");
            } else {
                header("Location: index.php?cmd=receivers&listid=$vallistid&phone=$fromphone");
            }
            exit();
        }
    }

    // If login fails
    $error = function_exists('__') ? __('login_error') : "Invalid username or password.";
    if (isset($stmt)) $stmt->close();
    mysqli_close($mysqli);
}
?>

<div class="login-container">
    <h2><?php echo htmlspecialchars(function_exists('__') ? __('login_title') : 'Log in', ENT_QUOTES, 'UTF-8'); ?></h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?cmd=login">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="user"><?php echo htmlspecialchars(function_exists('__') ? __('login_username') : 'Username', ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" name="user" id="user" autocomplete="username" required autofocus />
        </div>
        <div class="form-group">
            <label for="password"><?php echo htmlspecialchars(function_exists('__') ? __('login_password') : 'Password', ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="password" name="password" id="password" autocomplete="current-password" required />
        </div>

        <button type="submit" name="submit" class="login-btn"><?php echo htmlspecialchars(function_exists('__') ? __('login_btn') : 'Log in', ENT_QUOTES, 'UTF-8'); ?></button>
    </form>
</div>
