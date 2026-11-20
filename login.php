<?php
// 1. Logik: Håndter validering af login (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    require_once 'init.php';

    // Rens input og begræns længde som i din originale kode
    $formuser = strtolower(substr($_POST['user'] ?? '', 0, 20));
    $formpw   = substr($_POST['password'] ?? '', 0, 20);

    $mysqli = mysqli_connect($DBhost, $DBuser, $DBpass, $DBName);
    mysqli_set_charset($mysqli, "utf8mb4");

    if (!$mysqli) {
        die("Forbindelsesfejl: " . mysqli_connect_error());
    }

    // Prepared Statement for at undgå SQL Injection
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

        // Tjek om brugernavn og kodeord matcher
        if ($valuser === $formuser && $valpw === $formpw) {
            
        // Start sessionen og husk brugeren
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

    // Hvis login fejler
    $error = "Ugyldigt brugernavn eller adgangskode.";
    if (isset($stmt)) $stmt->close();
    mysqli_close($mysqli);
}
?>

<div class="login-container">
    <h2>Log ind</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?cmd=login">
        <div class="form-group">
            <label for="user">Brugernavn</label>
            <input type="text" name="user" id="user" autocomplete="username" required autofocus />
        </div>
        <div class="form-group">
            <label for="password">Adgangskode</label>
            <input type="password" name="password" id="password" autocomplete="current-password" required />
        </div>

        <button type="submit" name="submit" class="login-btn">Log ind</button>
    </form>
</div>