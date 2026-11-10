<?php
/**
 * FARASTORE - LOGOUT ADMIN
 */

require_once __DIR__ . '/../config.php';

// Hapus hanya session admin.
unset($_SESSION['admin_id'], $_SESSION['admin_username']);

session_regenerate_id(true);

header('Location: login.php');
exit;
