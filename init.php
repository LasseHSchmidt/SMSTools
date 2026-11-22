<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'lang.php';

// Generate a cryptographically secure CSRF token for the active session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field(): string {
    $token = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function verify_csrf(): void {
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postToken    = $_POST['csrf_token'] ?? '';
    if (empty($sessionToken) || !is_string($postToken) || !hash_equals($sessionToken, $postToken)) {
        http_response_code(403);
        die('Invalid or missing CSRF token. Please reload the page and try again.');
    }
}

$cmd        = isset($_GET['cmd']) ? $_GET['cmd'] : 'receivers';

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Redirect to login page if not authenticated
if (!$isLoggedIn && $cmd !== 'login') {
    $cmd = "login";
} elseif ($isLoggedIn && !empty($_SESSION['must_change_password'])) {
    // Force password change before allowing access to any other page
    $cmd = "changepassword";
}

// Retrieve IDs from session
$listid     = isset($_SESSION['listid']) ? $_SESSION['listid'] : 0;
$fromphone  = isset($_SESSION['phone'])  ? $_SESSION['phone']  : 0;

$DBhost     = Config::dbHost();
$DBuser     = Config::dbUser();
$DBpass     = Config::dbPass();
$DBName     = Config::dbName();
$TListName  = Config::TABLE_LISTNAME;
$TReceivers = Config::TABLE_RECEIVERS;
$TSendSMS   = Config::TABLE_SENDSMS;
$url        = Config::apiUrl();
$api_token  = Config::apiToken();
