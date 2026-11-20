<?php
session_start();
require_once 'config.php';

$cmd        = isset($_GET['cmd'])    ? $_GET['cmd']    : 'receivers';

// Tjek om brugeren er logget ind
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Hvis ikke logget ind, og de ikke allerede prøver at logge ind, send dem til login-siden
if (!$isLoggedIn && $cmd !== 'login') {
    $cmd = "login";
}

// Hent ID'er fra sessionen i stedet for kun at stole på URL'en
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
