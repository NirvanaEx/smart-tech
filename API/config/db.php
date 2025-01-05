<?php

// Конфигурация подключения к базе данных
$config = [
    'host' => 'localhost',
    'port' => '3306',
    'dbname' => 'smart_tech_market',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];

// Функция подключения к базе данных
if (!function_exists('getDatabaseConnection')) {
    function getDatabaseConnection() {
        global $config;

        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
