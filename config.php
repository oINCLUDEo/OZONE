<?php
// Настройки БД
$host = 'localhost';
$dbname = 'marketplace';
$user = 'postgres';
$password = '1';

// Создаём подключение
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

define('SITE_NAME', 'OZONE');
const UPLOAD_DIR = 'uploads/';