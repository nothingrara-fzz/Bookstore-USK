<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

define('DB_HOST', 'localhost');
define('DB_NAME', 'farastore');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal. Pastikan database farastore sudah di-import dari database.sql. Detail: " . htmlspecialchars($e->getMessage()));
}

function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header("Location: $url"); exit; }
function is_logged_in() { return isset($_SESSION['user_id']); }
function require_login() { if (!is_logged_in()) redirect('login.php'); }
function require_admin() { if (empty($_SESSION['admin_id'])) redirect('admin/login.php'); }
function rupiah($n) { return 'Rp' . number_format((float)$n, 0, ',', '.'); }
function current_user() {
    global $pdo;
    if (!is_logged_in()) return null;
    $s=$pdo->prepare("SELECT * FROM users WHERE id=?"); $s->execute([$_SESSION['user_id']]); return $s->fetch();
}
function cart_count() {
    global $pdo;
    if (!is_logged_in()) return 0;
    $s=$pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id=?"); $s->execute([$_SESSION['user_id']]); return (int)$s->fetchColumn();
}
function flash($key, $value=null) {
    if ($value !== null) { $_SESSION['flash'][$key]=$value; return; }
    $v=$_SESSION['flash'][$key]??null; unset($_SESSION['flash'][$key]); return $v;
}
