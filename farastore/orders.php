
<?php
require_once 'config.php';
require_login();

/* =========================================
   1. AMBIL RIWAYAT PESANAN USER
========================================= */

$s = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$s->execute([$_SESSION['user_id']]);
$orders = $s->fetchAll();

/* =========================================
   2. FUNGSI MENENTUKAN STATUS PESANAN
========================================= */

function getOrderProgress($status)
{
    $status = strtolower(trim($status));

    if (strpos($status, 'batal') !== false) {
        return [
            'class' => 'cancelled',
            'label' => 'Dibatalkan',
            'step' => -1
        ];
    }

    if (strpos($status, 'selesai') !== false) {
        return [
            'class' => 'completed',
            'label' => 'Selesai',
            'step' => 3
        ];
    }

    if (strpos($status, 'kirim') !== false) {
        return [
            'class' => 'shipping',
            'label' => 'Dikirim',
            'step' => 2
        ];
    }

    if (strpos($status, 'kemas') !== false) {
        return [
            'class' => 'process',
            'label' => 'Dikemas',
            'step' => 1
        ];
    }

    if (strpos($status, 'proses') !== false) {
        return [
            'class' => 'process',
            'label' => 'Diproses',
            'step' => 1
        ];
    }

    return [
        'class' => 'waiting',
        'label' => $status !== '' ? ucwords($status) : 'Menunggu Konfirmasi',
        'step' => 0
    ];
}

include 'header.php';
?>

<div class="history-page">

    <!-- HEADER HALAMAN -->
    <div class="history-heading">
        <div>
            <span class="history-eyebrow">ORDER HISTORY</span>
            <h1>Pesanan <em>Kamu</em></h1>
        </div>

        <a href="books.php" class="shop-again-button">
            <i class="fas fa-book-open"></i>
            Belanja lagi
        </a>
    </div>

    <?php if (!$orders): ?>

        <!-- JIKA BELUM ADA PESANAN -->
        <div class="history-empty">
            <div class="empty-icon">
                <i class="fas fa-book-open"></i>
            </div>

            <h2>Belum ada pesanan</h2>
            <p>Buku yang kamu pesan akan muncul di halaman ini.</p>

            <a href="books.php" class="primary-button">
                Cari buku sekarang
            </a>
        </div>

    <?php else: ?>

        <div class="history-list">

            <?php foreach ($orders as $o): ?>

                <?php
                /* Ambil buku dalam pesanan */
                $itemQuery = $pdo->prepare("
                    SELECT
                        oi.book_id,
                        oi.book_title,
                        oi.book_author,
                        oi.price,
                        oi.quantity,
                        b.image AS display_image
                    FROM order_items oi
                    LEFT JOIN books b ON b.id = oi.book_id
                    WHERE oi.order_id = ?
                ");

                $itemQuery->execute([$o['id']]);
                $items = $itemQuery->fetchAll();

                $progress = getOrderProgress($o['status']);
                ?>

                <article class="history-card">

                    <!-- BAGIAN INFORMASI BUKU -->
                    <div class="history-products">

                        <div class="order-number">
                            <span>NOMOR PESANAN</span>
                            <strong><?= e($o['order_code']) ?></strong>

                            <small>
                                <i class="far fa-calendar-alt"></i>
                                <?= date('d M Y, H:i', strtotime($o['created_at'])) ?>
                            </small>
                        </div>

                        <div class="book-list">

                            <?php if ($items): ?>

                                <?php foreach ($items as $it): ?>

                                    <?php
                                    $bookImage = trim($it['display_image'] ?? '');

                                    if ($bookImage === '') {
                                        $bookImage = 'img/no-image.png';
                                    }
                                    ?>

                                    <div class="history-book">

                                        <div class="history-book-cover">
                                            <img
                                                src="<?= e($bookImage) ?>"
                                                alt="<?= e($it['book_title']) ?>"
                                                onerror="this.onerror=null;this.src='img/no-image.png';"
                                            >
                                        </div>

                                        <div class="history-book-info">
                                            <strong><?= e($it['book_title']) ?></strong>

                                            <span>
                                                <?= e($it['book_author']) ?>
                                            </span>

                                            <small>
                                                <?= (int)$it['quantity'] ?> buku
                                                × <?= rupiah($it['price']) ?>
                                            </small>
                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <p class="no-book">Detail buku tidak ditemukan.</p>

                            <?php endif; ?>

                        </div>

                        <div class="order-payment">
                            <span class="payment-method">
                                <i class="fas fa-wallet"></i>
                                <?= e($o['payment_method']) ?>
                            </span>

                            <?php if (!empty($o['bank_name'])): ?>
                                <span>· <?= e($o['bank_name']) ?></span>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- BAGIAN STATUS -->
                    <div class="history-status">

                        <span class="status-badge <?= e($progress['class']) ?>">
                            <span class="status-dot"></span>
                            <?= e($progress['label']) ?>
                        </span>

                        <?php if ($progress['step'] >= 0): ?>

                            <div class="progress-track">

                                <?php
                                $steps = [
                                    'Pesanan<br>Diterima',
                                    'Diproses',
                                    'Dikirim',
                                    'Selesai'
                                ];
                                ?>

                                <div class="progress-line">
                                    <div
                                        class="progress-line-fill"
                                        style="width: <?= ($progress['step'] / 3) * 100 ?>%;"
                                    ></div>
                                </div>

                                <?php foreach ($steps as $index => $step): ?>

                                    <div class="progress-step <?= $index <= $progress['step'] ? 'active' : '' ?>">

                                        <span class="step-circle">
                                            <?php if ($index < $progress['step']): ?>
                                                <i class="fas fa-check"></i>
                                            <?php elseif ($index === $progress['step']): ?>
                                                <i class="fas fa-circle"></i>
                                            <?php endif; ?>
                                        </span>

                                        <small><?= $step ?></small>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php else: ?>

                            <div class="cancelled-message">
                                <i class="fas fa-times-circle"></i>
                                Pesanan ini telah dibatalkan.
                            </div>

                        <?php endif; ?>

                    </div>

                    <!-- BAGIAN TOTAL DAN TOMBOL -->
                    <div class="history-total">

                        <span>Total Pembayaran</span>

                        <strong class="total-price">
                            <?= rupiah($o['total']) ?>
                        </strong>

                        <div class="history-actions">

                            <a
                                href="order_detail.php?id=<?= (int)$o['id'] ?>"
                                class="detail-button"
                            >
                                Lihat Detail
                                <i class="fas fa-arrow-right"></i>
                            </a>

                            <a
                                href="receipt.php?id=<?= (int)$o['id'] ?>"
                                class="receipt-button"
                            >
                                <i class="fas fa-file-pdf"></i>
                                Unduh Struk PDF
                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<style>

/* =========================================
   FARASTORE - ORDER HISTORY MODERN
========================================= */

.history-page {
    width: 100%;
    max-width: 1450px;
    margin: 35px auto 65px;
    padding: 0 35px;
    box-sizing: border-box;
    color: #302438;
}

/* HEADER */

.history-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.history-eyebrow {
    display: block;
    color: #df6890;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 8px;
}

.history-heading h1 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 34px;
    color: #302438;
}

.history-heading h1 em {
    color: #df6890;
}

.history-heading p {
    margin: 8px 0 0;
    color: #938694;
    font-size: 14px;
}

.shop-again-button {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 13px 19px;
    border: 1px solid #f0bfd0;
    border-radius: 12px;
    background: white;
    color: #d6537e;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    transition: .2s;
}

.shop-again-button:hover {
    background: #fff0f5;
    transform: translateY(-2px);
}

/* ORDER CARD */

.history-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.history-card {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(230px, .9fr) minmax(240px, .9fr);
    align-items: center;
    gap: 25px;
    padding: 27px;
    background: #fff;
    border: 1px solid #f0dce5;
    border-radius: 22px;
    box-shadow: 0 8px 25px rgba(80, 40, 60, .035);
    transition: .25s;
}

.history-card:hover {
    border-color: #edabc2;
    box-shadow: 0 12px 30px rgba(80, 40, 60, .07);
    transform: translateY(-2px);
}

/* BOOKS */

.history-products {
    min-width: 0;
}

.order-number {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 17px;
}

.order-number > span {
    color: #a18f9c;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.3px;
}

.order-number > strong {
    color: #34283a;
    font-size: 16px;
    overflow-wrap: anywhere;
}

.order-number small {
    color: #948795;
    font-size: 12px;
}

.order-number small i {
    margin-right: 5px;
    color: #df6890;
}

.book-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.history-book {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
    max-width: 100%;
}

.history-book-cover {
    width: 55px;
    height: 76px;
    flex-shrink: 0;
    padding: 3px;
    box-sizing: border-box;
    overflow: hidden;
    border: 1px solid #f1dfe7;
    border-radius: 8px;
    background: #fff5f8;
}

.history-book-cover img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 5px;
}

.history-book-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.history-book-info strong {
    color: #382b3d;
    font-size: 12px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.history-book-info span {
    color: #968a98;
    font-size: 11px;
}

.history-book-info small {
    color: #d35d85;
    font-size: 10px;
}

.no-book {
    color: #998d98;
    font-size: 12px;
}

.order-payment {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 15px;
    color: #968895;
    font-size: 11px;
}

.payment-method i {
    margin-right: 5px;
    color: #df6890;
}

/* STATUS */

.history-status {
    min-width: 0;
    padding: 8px 22px;
    border-left: 1px solid #f0dfe7;
    border-right: 1px solid #f0dfe7;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 11px 17px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 800;
}

.status-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: currentColor;
}

.status-badge.completed {
    background: #e6f6ee;
    color: #268b61;
}

.status-badge.shipping {
    background: #edf2ff;
    color: #5576c5;
}

.status-badge.process {
    background: #fff4e7;
    color: #c18136;
}

.status-badge.waiting {
    background: #fff0f5;
    color: #d6537e;
}

.status-badge.cancelled {
    background: #fff0f0;
    color: #d45a5a;
}

/* PROGRESS */

.progress-track {
    position: relative;
    display: flex;
    justify-content: space-between;
    margin-top: 28px;
}

.progress-line {
    position: absolute;
    top: 9px;
    left: 9px;
    right: 9px;
    height: 3px;
    background: #eee8ed;
    border-radius: 5px;
}

.progress-line-fill {
    height: 100%;
    background: #e56b97;
    border-radius: 5px;
    transition: .3s;
}

.progress-step {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 25%;
    gap: 9px;
    text-align: center;
}

.step-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border: 3px solid #eee8ed;
    border-radius: 50%;
    background: #eee8ed;
    color: white;
    box-sizing: border-box;
}

.progress-step.active .step-circle {
    border-color: #e56b97;
    background: #e56b97;
}

.progress-step.active .step-circle i {
    font-size: 6px;
}

.progress-step small {
    color: #a095a0;
    font-size: 9px;
    font-weight: 600;
    line-height: 1.4;
}

.progress-step.active small {
    color: #594253;
}

.cancelled-message {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-top: 20px;
    padding: 12px;
    border-radius: 10px;
    background: #fff0f0;
    color: #d45a5a;
    font-size: 12px;
}

/* TOTAL */

.history-total {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
}

.history-total > span {
    color: #9a8c9a;
    font-size: 12px;
    margin-bottom: 7px;
}

.total-price {
    color: #d84f7b;
    font-size: 25px;
    font-weight: 800;
    margin-bottom: 18px;
}

/* ACTIONS */

.history-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    width: 100%;
}

.detail-button,
.receipt-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 43px;
    padding: 0 15px;
    box-sizing: border-box;
    border-radius: 11px;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
    transition: .2s;
}

.detail-button {
    background: #df6890;
    color: white;
    border: 1px solid #df6890;
}

.detail-button:hover {
    background: #ca4e79;
    color: white;
}

.receipt-button {
    background: white;
    color: #d6537e;
    border: 1px solid #edabc2;
}

.receipt-button:hover {
    background: #fff0f5;
}

/* EMPTY STATE */

.history-empty {
    padding: 65px 20px;
    text-align: center;
    background: white;
    border: 1px solid #f0dce5;
    border-radius: 22px;
}

.empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 65px;
    height: 65px;
    margin: 0 auto 18px;
    border-radius: 20px;
    background: #fff0f5;
    color: #df6890;
    font-size: 25px;
}

.history-empty h2 {
    margin: 0 0 8px;
    color: #34283a;
    font-family: 'Playfair Display', serif;
}

.history-empty p {
    margin: 0 0 20px;
    color: #998d98;
    font-size: 13px;
}

.primary-button {
    display: inline-flex;
    padding: 12px 20px;
    border-radius: 10px;
    background: #df6890;
    color: white;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

/* TABLET */

@media (max-width: 1100px) {
    .history-card {
        grid-template-columns: 1fr 1fr;
    }

    .history-status {
        border-right: 0;
    }

    .history-total {
        grid-column: 1 / -1;
        padding-top: 20px;
        border-top: 1px solid #f0dfe7;
    }
}

/* MOBILE */

@media (max-width: 650px) {
    .history-page {
        margin-top: 22px;
        padding: 0 15px;
    }

    .history-heading {
        align-items: flex-start;
    }

    .history-heading h1 {
        font-size: 27px;
    }

    .shop-again-button {
        padding: 10px 12px;
        font-size: 11px;
        white-space: nowrap;
    }

    .history-card {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 19px;
        border-radius: 17px;
    }

    .history-status {
        padding: 18px 0;
        border-left: 0;
        border-right: 0;
        border-top: 1px solid #f0dfe7;
        border-bottom: 1px solid #f0dfe7;
    }

    .history-total {
        grid-column: auto;
        padding-top: 0;
        border-top: 0;
    }

    .history-actions {
        flex-direction: column;
    }

    .detail-button,
    .receipt-button {
        width: 100%;
    }
}

</style>

<?php include 'footer.php'; ?>
