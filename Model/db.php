<?php
require_once __DIR__ . '/../config/db_config.php';

$host     = defined('DB_HOST') ? DB_HOST : 'localhost';
$username = defined('DB_USER') ? DB_USER : 'root';
$password = defined('DB_PASS') ? DB_PASS : '';
$database = defined('DB_NAME') ? DB_NAME : 'vitaguard_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
