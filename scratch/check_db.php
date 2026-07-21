<?php
require_once __DIR__ . '/../src/config/config.php';
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_PORT: " . DB_PORT . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . DB_PASS . "\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    echo "Direct PDO Connection successful!\n";
} catch (PDOException $e) {
    echo "Direct PDOException: " . $e->getMessage() . "\n";
}
