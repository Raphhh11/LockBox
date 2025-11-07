<?php
// db.php

$host = 'localhost'; // atau 'localhost'
$db   = 'crypt'; // Ganti dengan nama database Anda
$user = 'root'; // Ganti dengan username DB Anda (misal: 'root')
$pass = ''; // Ganti dengan password DB Anda
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Mulai session di sini agar semua file yang menyertakan db.php otomatis punya session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>