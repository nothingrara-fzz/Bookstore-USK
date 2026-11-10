
<?php
require_once 'config.php';
require_login();

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

$s = $pdo->prepare("
    SELECT oi.*, b.image AS display_image
    FROM order_items oi
    LEFT JOIN books b ON b.id = oi.book_id
    WHERE oi.order_id = ?
");

$s->execute([$o['id']]);
$items = $s->fetchAll();

$status = strtolower(trim($o['status']));
$statusClass = 'status-process';

if (strpos($status, 'selesai') !== false) {
    $statusClass = 'status-success';
} elseif (strpos($status, 'batal') !== false) {
    $statusClass = 'status-cancelled';
} elseif (strpos($status, 'kirim') !== false) {
    $statusClass = 'status-shipping';
}

// Format tanggal pesanan
$tanggalPesanan = !empty($o['created_at'])
    ? date('d-m-Y', strtotime($o['created_at']))
    : '-';

include 'header.php';
?>

<div class="order-detail-page">

    <div class="order-detail-heading">
        <div>
            <span class="order-eyebrow">FARASTORE · INFORMASI PESANAN</span>
            <h1>Detail <em>pesanan.</em></h1>

            <p>
                Tanggal pesanan:
                <strong><?= e($tanggalPesanan) ?></strong>
                <br>
                Nomor pesanan:
                <strong><?= e($o['order_code']) ?></strong>
            </p>
        </div>

        <a href="orders.php" class="back-order-button">
            ← Kembali
        </a>
    </div>

    <section class="order-detail-card">

        <div class="order-card-heading">
            <div>
                <span class="section-label">INFORMASI PESANAN</span>
                <h2>Pesanan kamu</h2>
                <p>Berikut rincian buku yang kamu pesan.</p>
            </div>

            <span class="order-status <?= $statusClass ?>">
                <span class="status-indicator"></span>
                <?= e($o['status']) ?>
            </span>
        </div>

        <div class="section-divider"></div>

        <div class="products-section">

            <div class="section-title">
                <div>
                    <span class="section-label">RINCIAN PEMBELIAN</span>
                    <h3>Produk yang dibeli</h3>
                </div>

                <span class="product-count">
                    <?= count($items) ?> produk
                </span>
            </div>

            <div class="product-list">

                <?php foreach ($items as $it): ?>
                    <?php
                    $bookImage = trim($it['display_image'] ?? '');

                    if ($bookImage === '') {
                        $bookImage = 'img/no-image.png';
                    }
                    ?>

                    <div class="product-row">

                        <div class="product-main">
                            <div class="product-cover">
                                <img
                                    src="<?= e($bookImage) ?>"
                                    alt="<?= e($it['book_title']) ?>"
                                    onerror="this.onerror=null;this.src='img/no-image.png';"
                                >
                            </div>

                            <div class="product-description">
                                <h3><?= e($it['book_title']) ?></h3>

                                <p class="product-author">
                                    <?= e($it['book_author']) ?>
                                </p>

                                <span class="product-price">
                                    <?= rupiah($it['price']) ?> / buku
                                </span>

                                <span class="product-quantity-mobile">
                                    Jumlah: <?= (int)$it['quantity'] ?> buku
                                </span>
                            </div>
                        </div>

                        <div class="product-quantity">
                            <span>Jumlah</span>
                            <strong><?= (int)$it['quantity'] ?> buku</strong>
                        </div>

                        <div class="product-subtotal">
                            <span>Subtotal</span>
                            <strong>
                                <?= rupiah($it['price'] * $it['quantity']) ?>
                            </strong>
                        </div>

                    </div>
                <?php endforeach; ?>

                <?php if (!$items): ?>
                    <div class="empty-products">
                        Belum ada produk dalam pesanan ini.
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <div class="order-information-grid">

            <div class="information-box">
                <div class="information-box-heading">
                    <div class="information-icon address-icon">⌖</div>

                    <div>
                        <span>INFORMASI PENGIRIMAN</span>
                        <h3>Alamat pengiriman</h3>
                    </div>
                </div>

                <div class="recipient-name">
                    <?= e($o['customer_name']) ?>
                </div>

                <div class="recipient-detail">
                    <p>
                        <span>☎</span>
                        <?= e($o['phone']) ?>
                    </p>

                    <p>
                        <span>⌖</span>
                        <span><?= nl2br(e($o['address'])) ?></span>
                    </p>
                </div>
            </div>

            <div class="information-box">
                <div class="information-box-heading">
                    <div class="information-icon payment-icon">♡</div>

                    <div>
                        <span>INFORMASI TRANSAKSI</span>
                        <h3>Metode pembayaran</h3>
                    </div>
                </div>

                <div class="payment-method-name">
                    <?= e($o['payment_method']) ?>

                    <?php if (!empty($o['bank_name'])): ?>
                        <span>· <?= e($o['bank_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="payment-total-small">
                    <span>Total pembayaran</span>
                    <strong><?= rupiah($o['total']) ?></strong>
                </div>

                <?php if (!empty($o['payment_proof'])): ?>

                    <div class="proof-container">
                        <span class="proof-label">
                            ✓ Bukti pembayaran
                        </span>

                        <button
                            type="button"
                            class="proof-image-button"
                            onclick="openProof()"
                        >
                            <img
                                src="<?= e($o['payment_proof']) ?>"
                                alt="Bukti pembayaran"
                            >
                            <span>Lihat bukti pembayaran ↗</span>
                        </button>
                    </div>

                <?php elseif (stripos($o['payment_method'], 'COD') !== false): ?>

                    <div class="payment-notice">
                        Pembayaran dilakukan saat pesanan diterima.
                    </div>

                <?php else: ?>

                    <div class="payment-notice">
                        Belum ada bukti pembayaran.
                    </div>

                <?php endif; ?>

            </div>

        </div>

        <div class="order-total-section">
            <div>
                <span>Total seluruh pesanan</span>
                <p>Terima kasih sudah berbelanja di Farastore ♡</p>
            </div>

            <strong><?= rupiah($o['total']) ?></strong>
        </div>

        <div class="order-actions">

            <button
                type="button"
                class="receipt-download"
                onclick="openReceipt()"
            >
                Lihat struk
            </button>

            <a class="back-order-link" href="orders.php">
                Kembali ke riwayat pesanan
            </a>

        </div>

    </section>
</div>

<?php if (!empty($o['payment_proof'])): ?>

<div class="proof-modal" id="proofModal">
    <div class="proof-modal-content">

        <button
            type="button"
            class="modal-close"
            onclick="closeProof()"
            aria-label="Tutup"
        >
            &times;
        </button>

        <h3>Bukti pembayaran</h3>
        <p>Berikut foto bukti pembayaran pesanan kamu.</p>

        <img
            src="<?= e($o['payment_proof']) ?>"
            alt="Bukti pembayaran"
        >

        <button
            type="button"
            class="modal-button"
            onclick="closeProof()"
        >
            Tutup
        </button>

    </div>
</div>

<?php endif; ?>

<div class="receipt-modal" id="receiptModal">
    <div class="receipt-modal-content">

        <button
            type="button"
            class="modal-close"
            onclick="closeReceipt()"
            aria-label="Tutup"
        >
            &times;
        </button>

        <div class="receipt-paper" id="receiptPaper">

            <div class="receipt-header">
                <div class="receipt-logo">f.</div>
                <h2>farastore</h2>
                <span>BUKU & KEBAHAGIAAN KECIL</span>
                <p>STRUK PEMBAYARAN</p>
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-info">

                <div>
                    <span>Tanggal pesanan</span>
                    <strong><?= e($tanggalPesanan) ?></strong>
                </div>

                <div>
                    <span>Nomor pesanan</span>
                    <strong><?= e($o['order_code']) ?></strong>
                </div>

                <div>
                    <span>Status pesanan</span>
                    <strong><?= e($o['status']) ?></strong>
                </div>

                <div>
                    <span>Nama penerima</span>
                    <strong><?= e($o['customer_name']) ?></strong>
                </div>

                <div>
                    <span>Nomor telepon</span>
                    <strong><?= e($o['phone']) ?></strong>
                </div>

                <div>
                    <span>Alamat pengiriman</span>
                    <strong><?= e($o['address']) ?></strong>
                </div>

                <div>
                    <span>Metode pembayaran</span>
                    <strong>
                        <?= e($o['payment_method']) ?>
                        <?= !empty($o['bank_name']) ? ' - ' . e($o['bank_name']) : '' ?>
                    </strong>
                </div>

            </div>

            <div class="receipt-divider"></div>

            <h3 class="receipt-products-title">Rincian pembelian</h3>

            <?php foreach ($items as $it): ?>
                <div class="receipt-product">
                    <div>
                        <strong><?= e($it['book_title']) ?></strong>
                        <span>
                            <?= (int)$it['quantity'] ?> ×
                            <?= rupiah($it['price']) ?>
                        </span>
                    </div>

                    <strong>
                        <?= rupiah($it['price'] * $it['quantity']) ?>
                    </strong>
                </div>
            <?php endforeach; ?>

            <div class="receipt-divider"></div>

            <div class="receipt-grand-total">
                <span>TOTAL PEMBAYARAN</span>
                <strong><?= rupiah($o['total']) ?></strong>
            </div>

            <div class="receipt-thanks">
                Terima kasih telah berbelanja di Farastore ♡
            </div>

        </div>

        <div class="receipt-modal-actions">
            <a
                href="receipt.php?id=<?= (int)$o['id'] ?>"
                class="receipt-pdf-button"
            >
                Unduh PDF
            </a>

            <button
                type="button"
                class="receipt-close-button"
                onclick="closeReceipt()"
            >
                Tutup
            </button>
        </div>

    </div>
</div>

<style>
.order-detail-page {
    width: 100%;
    max-width: 1120px;
    margin: 35px auto 60px;
    color: #302438;
}

.order-detail-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
}

.order-eyebrow,
.section-label {
    display: block;
    color: #d95b83;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
}

.order-detail-heading h1 {
    margin: 0;
    font-size: 30px;
}

.order-detail-heading h1 em {
    color: #df6890;
    font-family: 'Playfair Display', serif;
}

.order-detail-heading p {
    color: #918895;
    font-size: 12px;
    margin-top: 9px;
    line-height: 1.9;
}

.back-order-button {
    padding: 11px 16px;
    border: 1px solid #f1dce5;
    border-radius: 10px;
    background: white;
    color: #d84f7c;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

.order-detail-card {
    background: white;
    border: 1px solid #f1e4ea;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 10px 35px rgba(70,35,52,.045);
}

.order-card-heading,
.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.order-card-heading h2,
.section-title h3 {
    margin: 0;
    color: #302438;
}

.order-card-heading h2 {
    font-size: 21px;
}

.order-card-heading p {
    color: #9b909b;
    font-size: 12px;
}

.order-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 15px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 700;
}

.status-indicator {
    width: 7px;
    height: 7px;
    background: currentColor;
    border-radius: 50%;
}

.status-success { background: #fff0e4; color: #ce8345; }
.status-process { background: #fff5df; color: #b98227; }
.status-shipping { background: #edf3ff; color: #5477c6; }
.status-cancelled { background: #fff0f0; color: #d45a5a; }

.section-divider {
    height: 1px;
    background: #f2e9ee;
    margin: 23px 0;
}

.products-section {
    margin-bottom: 28px;
}

.section-title {
    margin-bottom: 13px;
}

.section-title h3 {
    font-size: 18px;
}

.product-count {
    padding: 7px 12px;
    background: #fff2f6;
    border-radius: 20px;
    color: #d65b83;
    font-size: 11px;
    font-weight: 700;
}

.product-list {
    border-top: 1px solid #f1e5eb;
}

.product-row {
    display: grid;
    grid-template-columns: minmax(0,1fr) 120px 160px;
    align-items: center;
    gap: 20px;
    padding: 18px 8px;
    border-bottom: 1px solid #f1e5eb;
}

.product-main {
    display: flex;
    align-items: center;
    gap: 17px;
    min-width: 0;
}

.product-cover {
    width: 90px;
    height: 120px;
    flex-shrink: 0;
    padding: 5px;
    border: 1px solid #f1e1e8;
    border-radius: 12px;
    background: #fff8fa;
    overflow: hidden;
}

.product-cover img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.product-description h3 {
    margin: 0 0 6px;
    font-family: 'Playfair Display',serif;
    font-size: 18px;
}

.product-author {
    color: #8f8491;
    font-size: 13px;
}

.product-price {
    color: #d16b8b;
    font-size: 12px;
}

.product-quantity,
.product-subtotal {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 7px;
}

.product-quantity > span,
.product-subtotal > span {
    color: #a196a0;
    font-size: 11px;
}

.product-quantity strong {
    padding: 8px 12px;
    border-radius: 8px;
    background: #fff1f5;
    color: #d6537e;
    font-size: 12px;
}

.product-subtotal strong {
    font-size: 14px;
}

.product-quantity-mobile {
    display: none;
}

.empty-products {
    padding: 25px;
    text-align: center;
    color: #999;
}

.order-information-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-top: 18px;
}

.information-box {
    min-width: 0;
    padding: 23px;
    border: 1px solid #f5e5ec;
    border-radius: 15px;
    background: #fff8fa;
}

.information-box-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
}

.information-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    font-size: 20px;
}

.address-icon { background: #fce7ef; color: #d6537e; }
.payment-icon { background: #f0eaff; color: #8b6bc0; }

.information-box-heading span {
    display: block;
    color: #a195a0;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.information-box-heading h3 {
    margin: 0;
    font-size: 15px;
}

.recipient-name {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 13px;
}

.recipient-detail {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.recipient-detail p {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 0;
    color: #8d828e;
    font-size: 12px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.recipient-detail p > span:first-child {
    color: #d75b83;
}

.payment-method-name {
    margin-bottom: 13px;
    font-size: 14px;
    font-weight: 700;
}

.payment-method-name span {
    color: #958894;
}

.payment-total-small {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 13px 0;
    border-top: 1px solid #f0e3e9;
    border-bottom: 1px solid #f0e3e9;
}

.payment-total-small span {
    color: #948995;
    font-size: 11px;
}

.payment-total-small strong {
    color: #d64e7a;
    font-size: 14px;
}

.proof-container {
    margin-top: 15px;
}

.proof-label {
    display: block;
    color: #299264;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 10px;
}

.proof-image-button {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    padding: 0;
    border: 0;
    background: transparent;
    color: #d6537e;
    font: inherit;
    font-size: 11px;
    cursor: pointer;
    text-align: left;
}

.proof-image-button img {
    width: 170px;
    height: 110px;
    object-fit: contain;
    background: white;
    border: 1px solid #f0dce5;
    border-radius: 10px;
}

.payment-notice {
    margin-top: 14px;
    padding: 12px;
    border-radius: 9px;
    background: #fff3df;
    color: #a87927;
    font-size: 11px;
    line-height: 1.6;
}

.order-total-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 25px;
    padding: 22px;
    background: #fff5f8;
    border: 1px solid #f6e4eb;
    border-radius: 14px;
}

.order-total-section span {
    font-size: 13px;
    font-weight: 700;
}

.order-total-section p {
    margin: 5px 0 0;
    color: #a0939f;
    font-size: 11px;
}

.order-total-section > strong {
    color: #d84f7b;
    font-size: 23px;
}

.order-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 13px;
    margin-top: 22px;
}

.receipt-download,
.receipt-pdf-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 13px 20px;
    background: #df6890;
    border: 0;
    border-radius: 10px;
    color: white;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.back-order-link {
    color: #d6537e;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

/* POPUP */

.proof-modal,
.receipt-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    padding: 20px;
    background: rgba(39,26,36,.65);
    align-items: center;
    justify-content: center;
    overflow-y: auto;
}

.proof-modal.show,
.receipt-modal.show {
    display: flex;
}

.proof-modal-content,
.receipt-modal-content {
    position: relative;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 28px;
    background: white;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
}

.proof-modal-content {
    text-align: center;
}

.proof-modal-content h3 {
    margin: 0 0 7px;
    color: #34283a;
}

.proof-modal-content p {
    color: #918591;
    font-size: 12px;
}

.proof-modal-content > img {
    display: block;
    max-width: 100%;
    max-height: 65vh;
    margin: 20px auto;
    object-fit: contain;
    border-radius: 10px;
}

.modal-close {
    position: absolute;
    top: 10px;
    right: 16px;
    border: 0;
    background: transparent;
    color: #8c7885;
    font-size: 29px;
    cursor: pointer;
}

.modal-button,
.receipt-close-button {
    padding: 12px 22px;
    border: 1px solid #f0dce5;
    border-radius: 10px;
    background: white;
    color: #d6537e;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

/* STRUK */

.receipt-paper {
    padding: 15px 12px;
    color: #34283a;
}

.receipt-header {
    text-align: center;
}

.receipt-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    border-radius: 50%;
    background: #e86f9b;
    color: white;
    font: italic 30px Georgia;
}

.receipt-header h2 {
    margin: 8px 0 0;
    font-size: 23px;
}

.receipt-header > span {
    color: #a18b9b;
    font-size: 9px;
    letter-spacing: 2px;
}

.receipt-header p {
    margin: 20px 0 10px;
    color: #d6537e;
    font-size: 12px;
    font-weight: 800;
}

.receipt-divider {
    border-top: 1px dashed #e7cbd7;
    margin: 17px 0;
}

.receipt-info {
    display: grid;
    gap: 12px;
}

.receipt-info div,
.receipt-product,
.receipt-grand-total {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    font-size: 12px;
}

.receipt-info span,
.receipt-product span {
    color: #918591;
}

.receipt-info strong {
    max-width: 60%;
    text-align: right;
    overflow-wrap: anywhere;
}

.receipt-products-title {
    margin-bottom: 13px;
    font-size: 14px;
}

.receipt-product {
    margin: 12px 0;
}

.receipt-product div {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.receipt-grand-total {
    color: #d6537e;
    font-size: 14px;
    font-weight: 800;
}

.receipt-thanks {
    margin-top: 25px;
    text-align: center;
    color: #806d7c;
    font: italic 13px Georgia;
}

.receipt-modal-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #f2e5eb;
}

@media (max-width: 760px) {
    .order-detail-page {
        margin-top: 22px;
        padding: 0 12px;
    }

    .order-detail-card {
        padding: 20px;
    }

    .order-information-grid {
        grid-template-columns: 1fr;
    }

    .product-row {
        grid-template-columns: minmax(0,1fr) auto;
        gap: 12px;
    }

    .product-quantity {
        display: none;
    }

    .product-quantity-mobile {
        display: block;
        margin-top: 7px;
        color: #918591;
        font-size: 10px;
    }

    .product-subtotal {
        grid-column: 2;
        grid-row: 1;
    }
}

@media (max-width: 520px) {
    .order-detail-heading {
        align-items: flex-start;
    }

    .order-detail-heading h1 {
        font-size: 25px;
    }

    .order-detail-card {
        padding: 16px;
    }

    .order-card-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .product-cover {
        width: 75px;
        height: 105px;
    }

    .product-main {
        gap: 11px;
    }

    .product-description h3 {
        font-size: 13px;
    }

    .product-subtotal strong {
        font-size: 11px;
    }

    .information-box {
        padding: 17px;
    }

    .order-total-section {
        align-items: flex-start;
        flex-direction: column;
        padding: 17px;
    }

    .order-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .receipt-download,
    .back-order-link {
        justify-content: center;
        width: 100%;
        box-sizing: border-box;
        padding: 13px;
        text-align: center;
    }

    .receipt-modal-content,
    .proof-modal-content {
        padding: 22px 15px;
    }

    .receipt-info div {
        font-size: 10px;
    }
}
</style>

<script>
function openProof() {
    const modal = document.getElementById('proofModal');

    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeProof() {
    const modal = document.getElementById('proofModal');

    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function openReceipt() {
    document.getElementById('receiptModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeReceipt() {
    document.getElementById('receiptModal').classList.remove('show');
    document.body.style.overflow = '';
}

document.querySelectorAll('.proof-modal, .receipt-modal').forEach(function(modal) {
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeProof();
        closeReceipt();
    }
});
</script>

<?php include 'footer.php'; ?>
