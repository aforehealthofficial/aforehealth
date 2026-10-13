<?php
$host = '127.0.0.1';
$port = '3306';
$user = 'root';
$pass = 'Dev@123';
$dbname = 'aforehealth';

try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$dbname}`;");
    
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($sql);
    
    echo "SUCCESS: Schema migrated successfully into database '{$dbname}'.\n";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES;");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Total tables created: " . count($tables) . "\n";
    echo implode(", ", $tables) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
