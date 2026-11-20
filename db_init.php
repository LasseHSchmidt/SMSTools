<?php
require_once '/var/www/html/config.php';

$host = Config::dbHost();
$user = Config::dbUser();
$pass = Config::dbPass();
$name = Config::dbName();

// Vent op til 30 sekunder på at MariaDB er klar
$mysqli = null;
for ($i = 0; $i < 30; $i++) {
    $mysqli = @mysqli_connect($host, $user, $pass, $name);
    if ($mysqli) break;
    sleep(1);
}

if (!$mysqli) {
    fwrite(STDERR, "Kunne ikke forbinde til databasen under opstart.\n");
    exit(1);
}

mysqli_set_charset($mysqli, "utf8mb4");

// 1. Opret tabeller hvis de ikke findes
$tGroups    = Config::TABLE_LISTNAME;
$tReceivers = Config::TABLE_RECEIVERS;
$tLog       = Config::TABLE_SENDSMS;

mysqli_query($mysqli, "
    CREATE TABLE IF NOT EXISTS `$tGroups` (
        ListId INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL,
        Password VARCHAR(50) NOT NULL,
        CellPhone VARCHAR(20) NOT NULL
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
");

mysqli_query($mysqli, "
    CREATE TABLE IF NOT EXISTS `$tReceivers` (
        ReceiverId INT AUTO_INCREMENT PRIMARY KEY,
        ListId INT NOT NULL,
        FullName VARCHAR(100) NOT NULL,
        ShortName VARCHAR(50) NOT NULL,
        CellPhone VARCHAR(20) NOT NULL,
        Phone VARCHAR(20) DEFAULT '0',
        Email VARCHAR(100) DEFAULT '',
        BirthDay VARCHAR(20) DEFAULT '0000-00-00'
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
");

mysqli_query($mysqli, "
    CREATE TABLE IF NOT EXISTS `$tLog` (
        SMSId INT AUTO_INCREMENT PRIMARY KEY,
        ListId INT NOT NULL,
        CellPhone VARCHAR(50) NOT NULL,
        Message TEXT NOT NULL,
        Status VARCHAR(50) DEFAULT 'Waiting',
        Date DATETIME NOT NULL
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
");

// 2. Indsæt eller opdater posten i SMSGroups baseret på .env
$groupId    = (int)(getenv('SMS_GROUP_ID') ?: 1);
$groupUser  = getenv('SMS_GROUP_USER');
$groupPass  = getenv('SMS_GROUP_PASS');
$groupPhone = getenv('SMS_GROUP_PHONE');

if ($groupUser && $groupPass && $groupPhone) {
    $stmt = $mysqli->prepare("
        INSERT INTO `$tGroups` (ListId, Username, Password, CellPhone)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            Username = VALUES(Username),
            Password = VALUES(Password),
            CellPhone = VALUES(CellPhone)
    ");
    $stmt->bind_param("isss", $groupId, $groupUser, $groupPass, $groupPhone);
    $stmt->execute();
    $stmt->close();
    echo "SMSGroups synkroniseret med .env for ListId=$groupId ($groupUser).\n";
}

mysqli_close($mysqli);
