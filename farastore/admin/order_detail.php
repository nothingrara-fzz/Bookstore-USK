<?php
/**
 * FARASTORE - DETAIL PESANAN ADMIN
 */

require_once __DIR__ . '/_layout.php';

$orderId = (int)($_GET['id'] ?? 0);

// Ambil data pesanan dan pelanggan
$query = $pdo->prepare("
    SELECT
        o.*,
        u.name AS user_name,
        u.email AS user_email
    FROM orders AS o
    INNER JOIN users AS u ON u.id = o.user_id
    WHERE o.id = ?
    LIMIT 1
");
$query->execute([$orderId]);
$order = $query->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Ambil item pesanan beserta gambar buku
$itemQuery = $pdo->prepare("
    SELECT
        oi.*,
        COALESCE(NULLIF(b.image, ''), oi.book_image) AS display_image
    FROM order_items AS oi
    LEFT JOIN books AS b ON b.id = oi.book_id
    WHERE oi.order_id = ?
");
$itemQuery->execute([$orderId]);
$items = $itemQuery->fetchAll();

// Periksa bukti pembayaran
$proof = trim((string)($order['payment_proof'] ?? ''));
$proof = ltrim(str_replace('\\', '/', $proof), '/');

$proofFile = dirname(__DIR__) . '/' . $proof;
$proofExists = ($proof !== '' && is_file($proofFile));

$proofUrl = '../' . implode(
    '/',
    array_map('rawurlencode', explode('/', $proof))
);

admin_header('Detail pesanan');
?>

<div class="detail-page">

    <div class="detail-page-header">
        <div>
            <span class="detail-eyebrow">FARASTORE ADMIN</span>
            <h1>Detail pesanan</h1>
            <p>Informasi lengkap transaksi dan produk yang dipesan.</p>
        </div>

        <a href="orders.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Kembali ke pesanan
        </a>
    </div>

    <section class="detail-card">

        <div class="order-top">
            <div>
                <span class="order-label">NOMOR PESANAN</span>
                <h2><?= e($order['order_code']) ?></h2>

                <p class="order-date">
                    <i class="far fa-calendar-alt"></i>
                    <?= date('d M Y · H:i', strtotime($order['created_at'])) ?>
                </p>
            </div>

            <span class="order-status">
                <span class="status-dot"></span>
                <?= e($order['status']) ?>
            </span>
        </div>

        <div class="detail-divider"></div>

        <div class="information-grid">

            <!-- DATA PENERIMA -->
            <div class="information-card">

                <div class="information-heading">
                    <div class="information-icon customer-icon">
                        <i class="fas fa-user"></i>
                    </div>

                    <div>
                        <span>INFORMASI PELANGGAN</span>
                        <h3>Data penerima</h3>
                    </div>
                </div>

                <div class="customer-name">
                    <?= e($order['customer_name']) ?>
                </div>

                <div class="information-list">

                    <div class="information-item">
                        <i class="far fa-envelope"></i>
                        <span><?= e($order['user_email']) ?></span>
                    </div>

                    <div class="information-item">
                        <i class="fas fa-phone-alt"></i>
                        <span><?= e($order['phone']) ?></span>
                    </div>

                    <div class="information-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= nl2br(e($order['address'])) ?></span>
                    </div>

                </div>
            </div>

            <!-- DETAIL PEMBAYARAN -->
            <div class="information-card">

                <div class="information-heading">
                    <div class="information-icon payment-icon">
                        <i class="fas fa-wallet"></i>
                    </div>

                    <div>
                        <span>INFORMASI PEMBAYARAN</span>
                        <h3>Detail transaksi</h3>
                    </div>
                </div>

                <div class="payment-method">
                    <?= e($order['payment_method']) ?>

                    <?php if (!empty($order['bank_name'])): ?>
                        <span>· <?= e($order['bank_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="payment-total">
                    <span>Total pembayaran</span>
                    <strong><?= rupiah($order['total']) ?></strong>
                </div>

                <div class="payment-proof-area">

                    <?php if ($proofExists): ?>

                        <span class="proof-accepted">
                            <i class="fas fa-check-circle"></i>
                            Bukti pembayaran diterima
                        </span>

                        <p class="proof-description">
                            Pelanggan telah mengunggah bukti pembayaran.
                        </p>

                        <button
                            type="button"
                            class="proof-open-button"
                            id="openProof"
                        >
                            <img
                                src="<?= e($proofUrl) ?>"
                                alt="Bukti pembayaran"
                            >

                            <span>
                                <i class="far fa-eye"></i>
                                Lihat bukti pembayaran
                            </span>
                        </button>

                        <!-- MODAL LIHAT BUKTI PEMBAYARAN -->
                        <div
                            class="proof-modal"
                            id="proofModal"
                            aria-hidden="true"
                        >
                            <div
                                class="proof-modal-content"
                                role="dialog"
                                aria-modal="true"
                                aria-label="Bukti pembayaran"
                            >

                                <button
                                    type="button"
                                    class="proof-modal-close"
                                    id="closeProof"
                                    aria-label="Tutup"
                                >
                                    &times;
                                </button>

                                <div class="modal-heading">
                                    <span>FARASTORE</span>
                                    <h3>Bukti pembayaran</h3>
                                    <p><?= e($order['order_code']) ?></p>
                                </div>

                                <img
                                    src="<?= e($proofUrl) ?>"
                                    alt="Bukti pembayaran ukuran penuh"
                                >

                            </div>
                        </div>

                    <?php elseif ($proof !== ''): ?>

                        <span class="proof-missing">
                            <i class="fas fa-exclamation-circle"></i>
                            File bukti tidak ditemukan
                        </span>

                        <p class="proof-description">
                            Periksa file di folder penyimpanan Farastore.
                        </p>

                    <?php elseif (
                        stripos($order['payment_method'], 'COD') !== false
                    ): ?>

                        <span class="cod-information">
                            <i class="fas fa-info-circle"></i>
                            COD — tidak membutuhkan bukti pembayaran.
                        </span>

                    <?php else: ?>

                        <span class="proof-missing">
                            <i class="fas fa-exclamation-circle"></i>
                            Pelanggan belum mengunggah bukti pembayaran.
                        </span>

                    <?php endif; ?>

                </div>
            </div>

        </div>

        <!-- ITEM PESANAN -->
        <div class="items-section">

            <div class="items-heading">
                <span class="detail-eyebrow">ORDER SUMMARY</span>
                <h2>Item pesanan</h2>
                <p><?= count($items) ?> jenis buku dalam pesanan ini.</p>
            </div>

            <div class="items-table">

                <div class="items-table-header">
                    <span>PRODUK</span>
                    <span>JUMLAH</span>
                    <span>TOTAL HARGA</span>
                </div>

                <?php foreach ($items as $item): ?>

                    <div class="order-row">

                        <div class="product-information">
                            <img
                                src="../<?= e($item['display_image'] ?: 'img/no-image.png') ?>"
                                alt="<?= e($item['book_title']) ?>"
                                onerror="this.onerror=null;this.src='../img/no-image.png';"
                            >

                            <div>
                                <h3><?= e($item['book_title']) ?></h3>
                                <p><?= e($item['book_author']) ?></p>

                                <span class="product-unit-price">
                                    <?= rupiah($item['price']) ?> / buku
                                </span>
                            </div>
                        </div>

                        <div class="product-quantity">
                            <span class="mobile-label">Jumlah</span>
                            <span class="quantity-badge">
                                <?= (int)$item['quantity'] ?> buku
                            </span>
                        </div>

                        <div class="product-total">
                            <span class="mobile-label">Total harga</span>
                            <strong>
                                <?= rupiah($item['price'] * $item['quantity']) ?>
                            </strong>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

            <div class="order-grand-total">
                <span>Total seluruh pesanan</span>
                <strong><?= rupiah($order['total']) ?></strong>
            </div>

        </div>

        <div class="detail-footer">
            <a href="orders.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Kembali ke daftar pesanan
            </a>

            <span>Terima kasih telah menggunakan Farastore.</span>
        </div>

        <!-- CETAK STRUK DILETAKKAN PALING BAWAH -->
        <div class="receipt-action">
            <button
                type="button"
                class="btn-print"
                id="openReceipt"
            >
                <i class="fas fa-print"></i>
                Cetak Struk
            </button>
        </div>

        <!-- MODAL CETAK STRUK PESANAN -->
        <div
            class="proof-modal"
            id="receiptModal"
            aria-hidden="true"
        >
            <div
                class="receipt-modal-content"
                role="dialog"
                aria-modal="true"
                aria-label="Cetak struk pesanan"
            >

                <button
                    type="button"
                    class="proof-modal-close receipt-close"
                    id="closeReceipt"
                    aria-label="Tutup"
                >
                    &times;
                </button>

                <iframe
                    id="receiptFrame"
                    title="Struk pesanan"
                    src="about:blank"
                ></iframe>

            </div>
        </div>

    </section>

</div>

<style>
.detail-page {
    width: 100%;
    max-width: 1500px;
    margin: 0 auto;
    color: #292333;
}

.detail-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
}

.detail-eyebrow {
    display: block;
    color: #df527b;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.7px;
    margin-bottom: 8px;
}

.detail-page-header h1 {
    margin: 0;
    color: #302438;
    font-size: 28px;
}

.detail-page-header p {
    margin: 8px 0 0;
    color: #918895;
    font-size: 13px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 17px;
    border: 1px solid #f2dce4;
    border-radius: 10px;
    background: white;
    color: #d94c75;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

.back-button:hover {
    background: #fff0f5;
}

.detail-card {
    padding: 28px;
    background: #fff;
    border: 1px solid #f0e5eb;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(65,35,50,.04);
}

.order-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.order-label {
    color: #a49aa5;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.3px;
}

.order-top h2 {
    margin: 8px 0;
    color: #302438;
    font-size: 19px;
}

.order-date {
    margin: 0;
    color: #99909c;
    font-size: 12px;
}

.order-date i {
    margin-right: 5px;
    color: #dc527b;
}

.order-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 30px;
    background: #eaf8ef;
    color: #218451;
    font-size: 12px;
    font-weight: 700;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
}

.detail-divider {
    height: 1px;
    margin: 24px 0;
    background: #f1e9ed;
}

.information-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: stretch;
    gap: 20px;
}

.information-card {
    min-width: 0;
    padding: 24px;
    border: 1px solid #f7e5ec;
    border-radius: 17px;
    background: #fff8fa;
}

.information-heading {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 24px;
}

.information-icon {
    display: flex;
    width: 45px;
    height: 45px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    font-size: 16px;
}

.customer-icon {
    color: #d94c75;
    background: #fce7ef;
}

.payment-icon {
    color: #8660bd;
    background: #f0e9ff;
}

.information-heading span {
    display: block;
    margin-bottom: 5px;
    color: #a69aa4;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 1px;
}

.information-heading h3 {
    margin: 0;
    color: #342a39;
    font-size: 16px;
}

.customer-name {
    margin-bottom: 18px;
    color: #342a39;
    font-size: 17px;
    font-weight: 700;
}

.information-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.information-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    color: #817784;
    font-size: 13px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.information-item i {
    width: 16px;
    flex-shrink: 0;
    margin-top: 3px;
    color: #d95a80;
}

.payment-method {
    margin-bottom: 18px;
    color: #342a39;
    font-size: 16px;
    font-weight: 700;
}

.payment-method span {
    color: #9a8997;
    font-weight: 500;
}

.payment-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 15px 0;
    border-top: 1px solid #f0e3e9;
    border-bottom: 1px solid #f0e3e9;
    color: #8e8290;
    font-size: 13px;
}

.payment-total strong {
    color: #d9436d;
    font-size: 18px;
}

.payment-proof-area {
    margin-top: 18px;
}

.proof-accepted,
.proof-missing,
.cod-information {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 13px;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 700;
}

.proof-accepted {
    color: #218451;
    background: #e9f8ef;
}

.proof-missing {
    color: #b66b00;
    background: #fff3df;
}

.cod-information {
    color: #77717c;
    background: #f1eff3;
}

.proof-description {
    margin: 12px 0;
    color: #9a909b;
    font-size: 12px;
    line-height: 1.6;
}

.proof-open-button {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    margin-top: 14px;
    padding: 0;
    border: 0;
    background: transparent;
    color: #d9436d;
    font-size: 13px;
    font-weight: 700;
    text-align: left;
    cursor: pointer;
}

.proof-open-button img {
    width: 210px;
    height: 135px;
    padding: 7px;
    object-fit: contain;
    border: 1px solid #f0d9e2;
    border-radius: 12px;
    background: white;
    transition: .2s;
}

.proof-open-button:hover img {
    border-color: #df527b;
    transform: translateY(-2px);
}

.proof-open-button span i {
    margin-right: 5px;
}

/* TOMBOL CETAK */
.btn-print {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    margin-top: 16px;
    padding: 12px 17px;
    border: none;
    border-radius: 10px;
    background: #df426b;
    color: white;
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
}

.btn-print:hover {
    background: #c93663;
    color: white;
    transform: translateY(-2px);
}

.receipt-action {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 22px;
    padding-top: 20px;
    border-top: 1px solid #f1e9ed;
}

.receipt-action .btn-print {
    margin-top: 0;
    padding: 13px 22px;
    font-size: 13px;
}

.items-section {
    margin-top: 34px;
}

.items-heading {
    margin-bottom: 18px;
}

.items-heading h2 {
    margin: 0;
    color: #302438;
    font-size: 21px;
}

.items-heading p {
    margin: 6px 0 0;
    color: #99909c;
    font-size: 12px;
}

.items-table {
    overflow: hidden;
    border: 1px solid #f0e8ed;
    border-radius: 13px;
}

.items-table-header,
.order-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 130px 160px;
    align-items: center;
    gap: 15px;
}

.items-table-header {
    padding: 15px 18px;
    background: #fff7fa;
    color: #a095a0;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1px;
}

.items-table-header span:nth-child(2),
.items-table-header span:nth-child(3) {
    text-align: right;
}

.order-row {
    padding: 18px;
    border-top: 1px solid #f1e9ed;
}

.product-information {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 0;
}

.product-information img {
    width: 65px;
    height: 85px;
    flex-shrink: 0;
    padding: 3px;
    object-fit: cover;
    border: 1px solid #eee1e7;
    border-radius: 7px;
    background: #fff7fa;
}

.product-information h3 {
    margin: 0 0 5px;
    color: #342a39;
    font-size: 14px;
}

.product-information p {
    margin: 0 0 5px;
    color: #968b97;
    font-size: 11px;
}

.product-unit-price {
    color: #aaa0aa;
    font-size: 10px;
}

.product-quantity,
.product-total {
    text-align: right;
}

.quantity-badge {
    display: inline-block;
    padding: 7px 11px;
    border-radius: 7px;
    background: #fff0f5;
    color: #d94c75;
    font-size: 11px;
    font-weight: 700;
}

.product-total strong {
    color: #342a39;
    font-size: 13px;
}

.mobile-label {
    display: none;
}

.order-grand-total {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 30px;
    padding: 22px 18px;
    border: 1px solid #f4e5eb;
    border-top: 0;
    border-radius: 0 0 13px 13px;
    background: #fff8fa;
}

.order-grand-total span {
    color: #8d818e;
    font-size: 13px;
    font-weight: 600;
}

.order-grand-total strong {
    color: #d9436d;
    font-size: 21px;
}

.detail-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #f1e9ed;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #d94c75;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.detail-footer > span {
    color: #aaa0aa;
    font-size: 11px;
}

/* POP-UP BUKTI PEMBAYARAN */
.proof-modal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(30,20,35,.72);
    backdrop-filter: blur(5px);
}

.proof-modal.show {
    display: flex;
}

.proof-modal-content {
    position: relative;
    width: min(800px, 95vw);
    max-height: 90vh;
    overflow: auto;
    padding: 30px;
    border-radius: 18px;
    background: white;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: modalAppear .2s ease;
}

@keyframes modalAppear {
    from {
        opacity: 0;
        transform: translateY(12px) scale(.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-heading span {
    color: #df527b;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
}

.modal-heading h3 {
    margin: 8px 0;
    color: #342a39;
    font-size: 21px;
}

.modal-heading p {
    color: #99909c;
    font-size: 12px;
}

.proof-modal-content > img {
    display: block;
    max-width: 100%;
    max-height: 60vh;
    margin: 22px auto;
    border-radius: 10px;
    object-fit: contain;
}

.proof-modal-close {
    position: absolute;
    top: 14px;
    right: 15px;
    z-index: 5;
    display: flex;
    width: 38px;
    height: 38px;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 50%;
    background: #fff0f5;
    color: #d9436d;
    font-size: 27px;
    cursor: pointer;
}

.proof-modal-close:hover {
    background: #fbdce7;
}

/* POP-UP CETAK: SAMA SEPERTI MODAL LIHAT BUKTI */
.receipt-modal-content {
    position: relative;
    width: min(1050px, 95vw);
    height: min(850px, 90vh);
    overflow: hidden;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: modalAppear .2s ease;
}

.receipt-modal-content iframe {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
    background: #f5f5f5;
}

.receipt-close {
    top: 10px;
    right: 10px;
}

body.proof-modal-open {
    overflow: hidden;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .information-grid {
        grid-template-columns: 1fr;
    }

    .receipt-modal-content {
        width: 96vw;
        height: 88vh;
    }
}

@media (max-width: 650px) {
    .detail-page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .detail-card {
        padding: 17px;
        border-radius: 15px;
    }

    .order-top {
        align-items: flex-start;
        flex-direction: column;
    }

    .information-card {
        padding: 18px;
    }

    .items-table-header {
        display: none;
    }

    .order-row {
        grid-template-columns: 1fr;
        gap: 13px;
        padding: 16px;
    }

    .product-information img {
        width: 58px;
        height: 77px;
    }

    .product-quantity,
    .product-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left;
    }

    .mobile-label {
        display: inline;
        color: #9a909b;
        font-size: 11px;
    }

    .order-grand-total {
        justify-content: space-between;
        gap: 10px;
        padding: 17px 13px;
    }

    .order-grand-total strong {
        font-size: 17px;
    }

    .detail-footer {
        align-items: flex-start;
        flex-direction: column;
    }

    .receipt-action {
        justify-content: stretch;
    }

    .receipt-action .btn-print {
        width: 100%;
    }

    .proof-modal-content {
        padding: 25px 15px;
    }

    .proof-open-button img {
        width: 100%;
        max-width: 230px;
    }

    .receipt-modal-content {
        width: 96vw;
        height: 85vh;
        border-radius: 13px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const proofModal = document.getElementById('proofModal');
    const openProof = document.getElementById('openProof');
    const closeProof = document.getElementById('closeProof');

    const receiptModal = document.getElementById('receiptModal');
    const openReceipt = document.getElementById('openReceipt');
    const closeReceipt = document.getElementById('closeReceipt');
    const receiptFrame = document.getElementById('receiptFrame');

    // Mengatur scroll halaman jika salah satu modal terbuka
    function updateBodyLock() {
        const proofIsOpen = proofModal && proofModal.classList.contains('show');
        const receiptIsOpen = receiptModal && receiptModal.classList.contains('show');

        document.body.classList.toggle(
            'proof-modal-open',
            proofIsOpen || receiptIsOpen
        );
    }

    // BUKA MODAL FOTO BUKTI PEMBAYARAN
    if (proofModal && openProof && closeProof) {

        function showProofModal() {
            proofModal.classList.add('show');
            proofModal.setAttribute('aria-hidden', 'false');

            updateBodyLock();
            closeProof.focus();
        }

        function hideProofModal() {
            proofModal.classList.remove('show');
            proofModal.setAttribute('aria-hidden', 'true');

            updateBodyLock();
            openProof.focus();
        }

        openProof.addEventListener('click', showProofModal);
        closeProof.addEventListener('click', hideProofModal);

        proofModal.addEventListener('click', function (event) {
            if (event.target === proofModal) {
                hideProofModal();
            }
        });
    }

    // BUKA MODAL CETAK STRUK PESANAN
    if (receiptModal && openReceipt && closeReceipt && receiptFrame) {

        function showReceiptModal() {

            // Muat halaman struk pesanan di dalam modal
            receiptFrame.src =
                'payment_receipt.php?id=<?= (int)$order['id'] ?>';

            receiptModal.classList.add('show');
            receiptModal.setAttribute('aria-hidden', 'false');

            updateBodyLock();
            closeReceipt.focus();
        }

        function hideReceiptModal() {

            receiptModal.classList.remove('show');
            receiptModal.setAttribute('aria-hidden', 'true');

            // Kosongkan iframe setelah modal ditutup
            receiptFrame.src = 'about:blank';

            updateBodyLock();
            openReceipt.focus();
        }

        openReceipt.addEventListener('click', showReceiptModal);
        closeReceipt.addEventListener('click', hideReceiptModal);

        receiptModal.addEventListener('click', function (event) {
            if (event.target === receiptModal) {
                hideReceiptModal();
            }
        });
    }

    // TUTUP MODAL DENGAN TOMBOL ESCAPE
    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }

        if (receiptModal && receiptModal.classList.contains('show')) {
            closeReceipt.click();
            return;
        }

        if (proofModal && proofModal.classList.contains('show')) {
            closeProof.click();
        }
    });

});
</script>

<?php admin_footer(); ?>