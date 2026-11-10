<?php

session_start();
date_default_timezone_set('Asia/Jakarta');

// Pengaturan database
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'farastore');
define('DB_USER', 'root');
define('DB_PASS', '');

// Membuat koneksi database
try {
    $dsn = 'mysql:host=' . DB_HOST
         . ';port=' . DB_PORT
         . ';dbname=' . DB_NAME
         . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

} catch (PDOException $error) {
    die('Koneksi database gagal: ' . $error->getMessage());
}