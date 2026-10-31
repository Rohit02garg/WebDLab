<?php
// Extra Lab Manual Section 6: Connecting PHP to MariaDB with PDO
$host = "127.0.0.1";
$port = 3306;
$dbname = "campus_events_db";
$user = "root";
$password = "";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    echo "Could not connect to the database.";
    exit;
}
?>
