
<?php
/**
 * FARASTORE - MEMPERBARUI STATUS PESANAN ADMIN
 * Admin hanya dapat mengubah status sampai DIKIRIM.
 * Status SELESAI hanya dapat dikonfirmasi oleh pelanggan.
 */

require_once __DIR__ . '/../config.php';
require_admin();

// ==========================================
// 1. CEK REQUEST
// ==========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

// ==========================================
// 2. AMBIL DATA
// ==========================================

$orderId = (int) ($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

// ==========================================
// 3. STATUS YANG BOLEH DIPILIH ADMIN
// ==========================================

$allowedStatuses = [
    'Menunggu Pembayaran',
    'Menunggu Verifikasi',
    'Pesanan Diproses',
    'Dikemas',
    'Dikirim',
    'Dibatalkan',
];

// ==========================================
// 4. VALIDASI
// ==========================================

if ($orderId <= 0 || !in_array($status, $allowedStatuses, true)) {
    header('Location: orders.php');
    exit;
}

// ==========================================
// 5. AMBIL STATUS PESANAN SAAT INI
// ==========================================

$query = $pdo->prepare("
    SELECT id, status
    FROM orders
    WHERE id = ?
    LIMIT 1
");

$query->execute([$orderId]);

$order = $query->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit;
}

// ==========================================
// 6. CEGAH ADMIN MENGUBAH PESANAN SELESAI
// ==========================================

if ($order['status'] === 'Selesai') {
    header('Location: orders.php');
    exit;
}

// ==========================================
// 7. UPDATE STATUS
// ==========================================

$update = $pdo->prepare("
    UPDATE orders
    SET status = ?
    WHERE id = ?
");

$update->execute([
    $status,
    $orderId
]);

// ==========================================
// 8. KEMBALI KE HALAMAN PESANAN
// ==========================================

header('Location: orders.php');
exit;
