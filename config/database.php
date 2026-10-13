<?php
require_once dirname(__DIR__) . '/helpers/env.php';
loadEnv();

function getDbConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $dbname = env('DB_DATABASE', 'aforehealth');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', 'Dev@123');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
        ];

        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            if (env('APP_DEBUG', false)) {
                die("Database Connection Error: " . $e->getMessage());
            } else {
                die("A database error occurred. Please try again later.");
            }
        }
    }

    return $pdo;
}
