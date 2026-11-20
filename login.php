<?php
// 1. Logic: Handle login validation (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    require_once 'init.php';

    // Sanitize input and limit length
    $formuser = strtolower(substr($_POST['user'] ?? '', 0, 20));
    $formpw   = substr($_POST['password'] ?? '', 0, 20);

    $mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
    mysqli_set_charset($mysqli, "utf8mb4");

    if (!$mysqli) {
        die("Connection error: " . mysqli_connect_error());
    }

    // Prepared statement to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT Username, Password, ListId, CellPhone FROM $TListName WHERE Username = ? LIMIT 1");
    $stmt->bind_param("s", $formuser);
    $stmt->execute();
    $result = $stmt->get_result();
    $line = $result->fetch_assoc();

    if ($line) {
        $valuser    = strtolower($line["Username"]);
        $valpw      = $line["Password"];
        $vallistid  = $line["ListId"];
        $fromphone  = $line["CellPhone"];

        // Check if username and password match
        if ($valuser === $formuser && $valpw === $formpw) {
            
            // Regenerate session ID and store user session data
            session_regenerate_id(true);
            $_SESSION['listid'] = $vallistid;
            $_SESSION['phone']  = $fromphone;
            $_SESSION['logged_in'] = true;
            
            $stmt->close();
            mysqli_close($mysqli);

            header("Location: index.php?cmd=receivers&listid=$vallistid&phone=$fromphone");
            exit();
        }
    }

    // Set translated error message if login fails
    $error = __('login_error');
    if (isset($stmt)) $stmt->close();
    mysqli_close($mysqli);
}
?>

<div class="login-container">
    <h2><?php echo htmlspecialchars(__('login_title')); ?></h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?cmd=login">
        <div class="form-group">
            <label for="user"><?php echo htmlspecialchars(__('login_username')); ?></label>
            <input type="text" name="user" id="user" autocomplete="username" required autofocus />
        </div>
        <div class="form-group">
            <label for="password"><?php echo htmlspecialchars(__('login_password')); ?></label>
            <input type="password" name="password" id="password" autocomplete="current-password" required />
        </div>

        <button type="submit" name="submit" class="login-btn"><?php echo htmlspecialchars(__('login_btn')); ?></button>
    </form>
</div>