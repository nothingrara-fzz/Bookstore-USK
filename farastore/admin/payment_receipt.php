
<?php
require_once '../config.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT o.*, u.name AS user_name, u.email AS user_email
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('Pesanan tidak ditemukan.');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$proof = trim((string)($order['payment_proof'] ?? ''));
$proof = ltrim(str_replace('\\', '/', $proof), '/');

$proofFile = dirname(__DIR__) . '/' . $proof;
$proofExists = $proof !== '' && is_file($proofFile);

$proofUrl = '../' . implode(
    '/',
    array_map('rawurlencode', explode('/', $proof))
);

function receiptRupiah($number) {
    return 'Rp ' . number_format((float)$number, 0, ',', '.');
}

$orderCode = preg_replace('/[^A-Za-z0-9_-]/', '-', $order['order_code']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($orderCode) ?> - FARASTORE</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px;
    background: #f2f2f2;
    font-family: Arial, sans-serif;
    color: #222;
}

.receipt {
    width: 800px;
    max-width: 100%;
    margin: auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
}

.header {
    text-align: center;
    border-bottom: 2px solid #ed4f78;
    padding-bottom: 20px;
    margin-bottom: 25px;
}

.header h1 {
    color: #ed4f78;
    margin: 0 0 8px;
}

.header p {
    margin: 0;
    color: #777;
}

h3 {
    margin-top: 25px;
    color: #ed4f78;
}

.info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.info div {
    padding: 12px;
    background: #fff7fa;
    border-radius: 8px;
}

.info small {
    display: block;
    color: #777;
    margin-bottom: 6px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    border-bottom: 1px solid #eee;
    padding: 12px 8px;
    text-align: left;
}

th {
    background: #fff0f5;
    color: #dc3d68;
}

.total {
    margin-top: 20px;
    padding: 18px;
    background: #fff0f5;
    border-radius: 10px;
    text-align: right;
    font-size: 20px;
    font-weight: bold;
    color: #dc3d68;
}

.proof {
    margin-top: 20px;
    text-align: center;
}

.proof img {
    max-width: 100%;
    max-height: 450px;
    object-fit: contain;
    border: 1px solid #ddd;
    padding: 8px;
    border-radius: 8px;
}

.footer {
    text-align: center;
    margin-top: 35px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    color: #777;
    font-size: 12px;
}

.print-button {
    display: block;
    margin: 20px auto;
    padding: 13px 25px;
    border: none;
    border-radius: 8px;
    background: #ed4f78;
    color: white;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.print-button:hover {
    background: #dc3d68;
}

@media (max-width: 600px) {
    body {
        padding: 12px;
    }

    .receipt {
        padding: 20px 15px;
    }

    .info {
        grid-template-columns: 1fr;
    }

    table {
        font-size: 12px;
    }

    th, td {
        padding: 9px 5px;
    }
}

@media print {
    @page {
        size: A4;
        margin: 12mm;
    }

    body {
        padding: 0;
        background: white;
    }

    .receipt {
        width: 100%;
        max-width: none;
        padding: 0;
        border-radius: 0;
    }

    .print-button {
        display: none !important;
    }

    .proof img {
        max-height: 350px;
    }

    tr, .info div, .total, .proof {
        break-inside: avoid;
    }
}
</style>
</head>

<body>

<button
    type="button"
    class="print-button"
    onclick="window.print()"
>
    Unduh / Simpan sebagai PDF
</button>

<div class="receipt">

    <div class="header">
        <h1>FARASTORE</h1>
        <p>Bukti Pembayaran dan Detail Pesanan</p>
    </div>

    <h3>Informasi Pesanan</h3>

    <div class="info">
        <div>
            <small>Nomor Pesanan</small>
            <strong><?= htmlspecialchars($order['order_code']) ?></strong>
        </div>

        <div>
            <small>Tanggal Pesanan</small>
            <strong><?= date('d-m-Y H:i', strtotime($order['created_at'])) ?></strong>
        </div>

        <div>
            <small>Nama Pelanggan</small>
            <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
        </div>

        <div>
            <small>Email</small>
            <strong><?= htmlspecialchars($order['user_email']) ?></strong>
        </div>

        <div>
            <small>Nomor Telepon</small>
            <strong><?= htmlspecialchars($order['phone']) ?></strong>
        </div>

        <div>
            <small>Metode Pembayaran</small>
            <strong>
                <?= htmlspecialchars($order['payment_method']) ?>
                <?= !empty($order['bank_name']) ? ' - ' . htmlspecialchars($order['bank_name']) : '' ?>
            </strong>
        </div>
    </div>

    <h3>Alamat Pengiriman</h3>
    <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>

    <h3>Rincian Buku</h3>

    <table>
        <thead>
            <tr>
                <th>Nama Buku</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['book_title']) ?></td>
                <td><?= receiptRupiah($item['price']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= receiptRupiah($item['price'] * $item['quantity']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        Total Pembayaran:
        <?= receiptRupiah($order['total']) ?>
    </div>

    <h3>Bukti Pembayaran dari User</h3>

    <div class="proof">
        <?php if ($proofExists): ?>
            <img
                src="<?= htmlspecialchars($proofUrl) ?>"
                alt="Bukti pembayaran"
            >
        <?php else: ?>
            <p>Bukti pembayaran tidak tersedia.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>Terima kasih telah berbelanja di FARASTORE.</p>
        <p>Dokumen ini merupakan bukti transaksi pesanan Farastore.</p>
    </div>

</div>

</body>
</html>
