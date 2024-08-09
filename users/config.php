<?php
// Database configuration
$host = 'localhost';
$db   = 'crayon';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Set DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    // Create a PDO instance
    $pdo = new PDO($dsn, $user, $pass);
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle the connection error
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}
?>
