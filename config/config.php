<?php
$db_host = 'localhost'; $db_name = 'oceano_db'; $db_user = 'root'; $db_pass = '';
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
if (session_status() === PHP_SESSION_NONE) session_start();
define('BASE_URL', '/oceano/'); define('APP_NAME', 'Oceano');
