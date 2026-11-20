<?php
    session_start();

    // 1. Tøm session-variablerne i hukommelsen
    $_SESSION = array();

    // 2. Hvis man vil være helt grundig, sletter man også session-cookien i browseren
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // 3. Ødelæg selve sessionen på serveren
    session_destroy();

    // 4. Viderestil og stop scriptet
    header("Location: index.php");
    exit(); // Meget vigtigt for at sikre, at koden stopper her
?>