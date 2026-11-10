
<?php
require_once 'config.php';
require_login();

// Ambil data pesanan
$s = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE id = ? AND user_id = ?
");

$s->execute([
    (int)($_GET['id'] ?? 0),
    $_SESSION['user_id']
]);

$o = $s->fetch();

if (!$o) {
    redirect('orders.php');
}

// Ambil produk pesanan
$s = $pdo->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = ?
");

$s->execute([$o['id']]);
$items = $s->fetchAll();

include 'header.php';
?>

<style>
.receipt-page {
    max-width: 850px;
    margin: 40px auto 60px;
    padding: 0 18px;
    color: #34283a;
}

.receipt-paper {
    background: #fff;
    border: 1px solid #f1dfe7;
    border-radius: 18px;
    padding: 38px;
    box-shadow: 0 12px 35px rgba(80, 40, 60, .07);
}

.receipt-heading {
    text-align: center;
    padding-bottom: 25px;
    border-bottom: 1px dashed #e9cbd8;
}

.receipt-logo {
    color: #df6890;
    font-family: 'Playfair Display', serif;
    font-size: 34px;
    font-weight: bold;
}

.receipt-heading p {
    color: #948494;
    font-size: 12px;
    margin: 6px 0;
}

.receipt-title {
    margin: 28px 0 18px;
    font-size: 22px;
    text-align: center;
}

.receipt-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 25px;
}

.receipt-info div {
    padding: 13px;
    background: #fff6f9;
    border-radius: 10px;
}

.receipt-info small {
    display: block;
    color: #998b98;
    font-size: 11px;
    margin-bottom: 6px;
}

.receipt-info strong {
    font-size: 13px;
    overflow-wrap: anywhere;
}

.receipt-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.receipt-table th,
.receipt-table td {
    padding: 13px 8px;
    border-bottom: 1px solid #f1e7ec;
    text-align: left;
    font-size: 12px;
}

.receipt-table th {
    color: #a17f91;
    background: #fff7fa;
}

.receipt-table td:last-child,
.receipt-table th:last-child {
    text-align: right;
}

.receipt-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 25px;
    padding: 20px;
    border-radius: 12px;
    background: #fff1f6;
}

.receipt-total span {
    font-weight: 700;
}

.receipt-total strong {
    color: #d94f7d;
    font-size: 23px;
}

.receipt-thanks {
    text-align: center;
    color: #a18d9b;
    font-size: 12px;
    margin-top: 28px;
}

.receipt-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 22px;
}

.receipt-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 9px;
    padding: 13px 20px;
    border-radius: 10px;
    background: #df6890;
    color: white;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    border: none;
    cursor: pointer;
}

.receipt-button:hover {
    background: #cc4e7a;
}

.receipt-back {
    color: #d6537e;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}

@media(max-width: 600px) {
    .receipt-paper {
        padding: 20px;
    }

    .receipt-info {
        grid-template-columns: 1fr;
    }

    .receipt-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .receipt-button,
    .receipt-back {
        text-align: center;
        justify-content: center;
        padding: 13px;
    }

    .receipt-total strong {
        font-size: 18px;
    }
}
</style>

<div class="receipt-page">

    <div class="receipt-paper">

        <div class="receipt-heading">
            <div class="receipt-logo">fara<span style="color:#34283a">store</span></div>
            <p>BOOKS & LITTLE JOYS</p>
            <p>Jl. Buku No. 123, Jakarta</p>
            <p>hello@farastore.id</p>
        </div>

        <h2 class="receipt-title">Struk Pembayaran</h2>

        <div class="receipt-info">

            <div>
                <small>Nomor Pesanan</small>
                <strong><?= e($o['order_code']) ?></strong>
            </div>

            <div>
                <small>Tanggal Pesanan</small>
                <strong>
                    <?= date('d-m-Y H:i', strtotime($o['created_at'])) ?>
                </strong>
            </div>

            <div>
                <small>Nama Penerima</small>
                <strong><?= e($o['customer_name']) ?></strong>
            </div>

            <div>
                <small>Nomor Telepon</small>
                <strong><?= e($o['phone']) ?></strong>
            </div>

            <div>
                <small>Metode Pembayaran</small>
                <strong><?= e($o['payment_method']) ?></strong>
            </div>

            <div>
                <small>Status Pesanan</small>
                <strong><?= e($o['status']) ?></strong>
            </div>

            <div style="grid-column:1/-1">
                <small>Alamat Pengiriman</small>
                <strong><?= nl2br(e($o['address'])) ?></strong>
            </div>

        </div>

        <h3>Rincian Pembelian</h3>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th>Nama Buku</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['book_title']) ?></td>
                    <td><?= rupiah($it['price']) ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td>
                        <?= rupiah($it['price'] * $it['quantity']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (!$items): ?>
                <tr>
                    <td colspan="4">Tidak ada rincian produk.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="receipt-total">
            <span>Total Pembayaran</span>
            <strong><?= rupiah($o['total']) ?></strong>
        </div>

        <p class="receipt-thanks">
            Terima kasih telah berbelanja di Farastore ♡
            <br>
            Semoga harimu selalu dipenuhi cerita indah.
        </p>

    </div>

    <div class="receipt-actions">

        <a href="order_detail.php?id=<?= (int)$o['id'] ?>"
           class="receipt-back">
            <i class="fas fa-arrow-left"></i>
            Kembali ke detail pesanan
        </a>

        <a href="receipt.php?id=<?= (int)$o['id'] ?>"
           class="receipt-button">
            <i class="fas fa-file-pdf"></i>
            Unduh Struk PDF
        </a>

    </div>

</div>

<?php include 'footer.php'; ?>
