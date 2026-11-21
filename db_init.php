<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once '/var/www/html/config.php';

$host = Config::dbHost();
$user = Config::dbUser();
$pass = Config::dbPass();
$name = Config::dbName();

// Wait up to 30 seconds for MariaDB to become ready
$mysqli = null;
for ($i = 0; $i < 30; $i++) {
    $mysqli = @mysqli_connect($host, $user, $pass, $name);
    if ($mysqli) break;
    sleep(1);
}

if (!$mysqli) {
    fwrite(STDERR, "Could not connect to database during startup.\n");
    exit(1);
}

mysqli_set_charset($mysqli, "utf8mb4");

// 1. Create required tables if they do not exist
$tGroups    = Config::TABLE_LISTNAME;
$tReceivers = Config::TABLE_RECEIVERS;
$tLog       = Config::TABLE_SENDSMS;

mysqli_query($mysqli, "
    CREATE TABLE IF NOT EXISTS `$tGroups` (
        ListId INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL,
        Password VARCHAR(255) NOT NULL,
        CellPhone VARCHAR(20) NOT NULL
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
");

// Ensure existing databases have enough space for password hashes
mysqli_query($mysqli, "ALTER TABLE `$tGroups` MODIFY Password VARCHAR(255) NOT NULL;");

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

// 2. Insert or update the default SMSGroups entry with a hashed password
$groupId    = (int)(getenv('SMS_GROUP_ID') ?: 1);
$groupUser  = getenv('SMS_GROUP_USER');
$groupPass  = getenv('SMS_GROUP_PASS');
$groupPhone = getenv('SMS_GROUP_PHONE');

if ($groupUser && $groupPass && $groupPhone) {
    $hashedPass = password_hash($groupPass, PASSWORD_DEFAULT);

    // Check if row already exists so we only re-hash when the password actually changes
    $checkStmt = $mysqli->prepare("SELECT Password FROM `$tGroups` WHERE ListId = ? LIMIT 1");
    $checkStmt->bind_param("i", $groupId);
    $checkStmt->execute();
    $res = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($res && password_verify($groupPass, $res['Password'])) {
        // Password matches existing hash; only update Username and CellPhone
        $stmt = $mysqli->prepare("UPDATE `$tGroups` SET Username = ?, CellPhone = ? WHERE ListId = ?");
        $stmt->bind_param("ssi", $groupUser, $groupPhone, $groupId);
    } else {
        // Insert new row or update with new hashed password
        $stmt = $mysqli->prepare("
            INSERT INTO `$tGroups` (ListId, Username, Password, CellPhone)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                Username = VALUES(Username),
                Password = VALUES(Password),
                CellPhone = VALUES(CellPhone)
        ");
        $stmt->bind_param("isss", $groupId, $groupUser, $hashedPass, $groupPhone);
    }

    $stmt->execute();
    $stmt->close();
    echo "SMSGroups synced with environment variables for ListId=$groupId ($groupUser).\n";
}

mysqli_close($mysqli);
