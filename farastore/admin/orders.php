
<?php
/**
 * FARASTORE - KELOLA PESANAN ADMIN
 */

require_once __DIR__ . '/_layout.php';

// ==========================================
// 1. AMBIL DATA PESANAN
// ==========================================

$orders = $pdo->query("
    SELECT
        o.id,
        o.order_code,
        o.payment_method,
        o.bank_name,
        o.payment_proof,
        o.total,
        o.status,
        o.created_at,
        u.name AS customer_name
    FROM orders o
    INNER JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
")->fetchAll();


// ==========================================
// 2. AMBIL PRODUK PESANAN
// ==========================================

$productQuery = $pdo->query("
    SELECT
        oi.order_id,
        oi.book_title,
        oi.quantity
    FROM order_items oi
    ORDER BY oi.id ASC
");

$allProducts = $productQuery->fetchAll();

$productsByOrder = [];

foreach ($allProducts as $product) {
    $productsByOrder[$product['order_id']][] = $product;
}


// ==========================================
// 3. STATUS PESANAN ADMIN
// ADMIN HANYA SAMPAI DIKIRIM
// ==========================================

$statuses = [
    'Menunggu Pembayaran',
    'Menunggu Verifikasi',
    'Pesanan Diproses',
    'Dikemas',
    'Dikirim',
    'Dibatalkan',
];


// ==========================================
// 4. STATISTIK
// ==========================================

$totalOrders = count($orders);
$waitingOrders = 0;
$processOrders = 0;
$completedOrders = 0;

foreach ($orders as $order) {
    $status = strtolower($order['status']);

    if (strpos($status, 'menunggu') !== false) {
        $waitingOrders++;
    } elseif (strpos($status, 'selesai') !== false) {
        $completedOrders++;
    } elseif (strpos($status, 'batal') === false) {
        $processOrders++;
    }
}

admin_header('Kelola pesanan');
?>

<div class="farastore-orders">

    <div class="orders-heading">
        <div>
            <span class="orders-eyebrow">FARASTORE ADMIN PANEL</span>
            <h1>Kelola <em>pesanan.</em></h1>
            <p>Pantau transaksi, produk, dan status pesanan pelanggan.</p>
        </div>

        <a href="index.php" class="orders-back">
            <i class="fas fa-arrow-left"></i>
            Dashboard
        </a>
    </div>


    <!-- STATISTIK -->
    <div class="orders-statistics">

        <div class="order-stat-card">
            <div class="stat-icon pink">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div>
                <span>Total pesanan</span>
                <strong><?= $totalOrders ?></strong>
                <small>Seluruh transaksi</small>
            </div>
        </div>

        <div class="order-stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <span>Menunggu</span>
                <strong><?= $waitingOrders ?></strong>
                <small>Perlu diperiksa</small>
            </div>
        </div>

        <div class="order-stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-box"></i>
            </div>
            <div>
                <span>Diproses</span>
                <strong><?= $processOrders ?></strong>
                <small>Sedang berjalan</small>
            </div>
        </div>

        <div class="order-stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <span>Selesai</span>
                <strong><?= $completedOrders ?></strong>
                <small>Dikonfirmasi pelanggan</small>
            </div>
        </div>

    </div>


    <!-- DAFTAR PESANAN -->
    <section class="orders-panel">

        <div class="orders-panel-heading">
            <div>
                <span class="orders-eyebrow">TRANSACTION MANAGEMENT</span>
                <h2>Daftar pesanan</h2>
                <p>Kelola semua pesanan yang masuk ke Farastore.</p>
            </div>

            <div class="orders-total-label">
                <i class="fas fa-receipt"></i>
                <?= $totalOrders ?> Pesanan
            </div>
        </div>


        <?php if (!$orders): ?>

            <div class="orders-empty">
                <div class="empty-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3>Belum ada pesanan</h3>
                <p>Pesanan pelanggan akan muncul di halaman ini.</p>
            </div>

        <?php else: ?>

            <div class="orders-table-wrap">

                <table class="modern-orders-table">

                    <thead>
                        <tr>
                            <th>Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Produk</th>
                            <th>Pembayaran</th>
                            <th>Bukti</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($orders as $order): ?>

                        <?php
                        $proof = trim(
                            (string)($order['payment_proof'] ?? '')
                        );

                        $proof = ltrim(
                            str_replace('\\', '/', $proof),
                            '/'
                        );

                        $proofFile = dirname(__DIR__) . '/' . $proof;

                        $proofExists = (
                            $proof !== '' &&
                            is_file($proofFile)
                        );

                        $isCOD = stripos(
                            $order['payment_method'] ?? '',
                            'COD'
                        ) !== false;

                        $statusText = strtolower($order['status']);

                        $statusClass = 'status-processing';

                        if (strpos($statusText, 'selesai') !== false) {
                            $statusClass = 'status-completed';
                        } elseif (strpos($statusText, 'batal') !== false) {
                            $statusClass = 'status-cancelled';
                        } elseif (strpos($statusText, 'kirim') !== false) {
                            $statusClass = 'status-shipping';
                        } elseif (strpos($statusText, 'menunggu') !== false) {
                            $statusClass = 'status-waiting';
                        }

                        $orderProducts = $productsByOrder[$order['id']] ?? [];

                        $totalItems = 0;

                        foreach ($orderProducts as $product) {
                            $totalItems += (int)$product['quantity'];
                        }

                        $isCompleted = (
                            $order['status'] === 'Selesai'
                        );
                        ?>

                        <tr>

                            <!-- KODE PESANAN -->
                            <td class="order-code-cell">
                                <strong>
                                    <?= e($order['order_code']) ?>
                                </strong>

                                <small>
                                    <?= date(
                                        'd M Y, H:i',
                                        strtotime($order['created_at'])
                                    ) ?>
                                </small>
                            </td>


                            <!-- PELANGGAN -->
                            <td>
                                <div class="customer-cell">

                                    <div class="customer-avatar">
                                        <?= e(strtoupper(substr($order['customer_name'], 0, 1))) ?>
                                    </div>

                                    <span>
                                        <?= e($order['customer_name']) ?>
                                    </span>

                                </div>
                            </td>


                            <!-- PRODUK -->
                            <td>
                                <div class="order-products">

                                    <?php if ($orderProducts): ?>

                                        <div class="product-names">

                                            <strong>
                                                <?= e($orderProducts[0]['book_title']) ?>
                                            </strong>

                                            <small>
                                                <?= $totalItems ?> buku

                                                <?php if (count($orderProducts) > 1): ?>
                                                    · <?= count($orderProducts) ?> jenis
                                                <?php endif; ?>
                                            </small>

                                            <?php if (count($orderProducts) > 1): ?>

                                                <div class="additional-products">

                                                    <?php foreach (array_slice($orderProducts, 1) as $product): ?>

                                                        <span>
                                                            <?= e($product['book_title']) ?>
                                                            (<?= (int)$product['quantity'] ?>)
                                                        </span>

                                                    <?php endforeach; ?>

                                                </div>

                                            <?php endif; ?>

                                        </div>

                                    <?php else: ?>

                                        <span class="muted">
                                            Produk tidak ditemukan
                                        </span>

                                    <?php endif; ?>

                                </div>
                            </td>


                            <!-- PEMBAYARAN -->
                            <td>
                                <div class="payment-cell">

                                    <strong>
                                        <?= e($order['payment_method']) ?>
                                    </strong>

                                    <?php if (!empty($order['bank_name'])): ?>
                                        <small>
                                            <?= e($order['bank_name']) ?>
                                        </small>
                                    <?php endif; ?>

                                </div>
                            </td>


                            <!-- BUKTI PEMBAYARAN -->
                            <td>

                                <?php if ($proofExists): ?>

                                    <a
                                        href="../<?= e($proof) ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="proof-button"
                                    >
                                        <i class="fas fa-image"></i>
                                        Lihat bukti
                                    </a>

                                <?php elseif ($isCOD): ?>

                                    <span class="proof-not-needed">
                                        Tidak perlu
                                    </span>

                                <?php else: ?>

                                    <span class="proof-missing">
                                        Belum tersedia
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- TOTAL -->
                            <td class="order-price">
                                <?= rupiah($order['total']) ?>
                            </td>


                            <!-- STATUS PESANAN -->
                            <td>

                                <?php if ($isCompleted): ?>

                                    <span class="completed-label">
                                        <i class="fas fa-check-circle"></i>
                                        Selesai
                                    </span>

                                    <small class="completed-info">
                                        Dikonfirmasi pelanggan
                                    </small>

                                <?php else: ?>

                                    <form
                                        class="status-form"
                                        method="post"
                                        action="update_order.php"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$order['id'] ?>"
                                        >

                                        <select
                                            name="status"
                                            class="<?= $statusClass ?>"
                                            aria-label="Status pesanan"
                                            required
                                        >

                                            <?php foreach ($statuses as $status): ?>

                                                <option
                                                    value="<?= e($status) ?>"
                                                    <?= $order['status'] === $status ? 'selected' : '' ?>
                                                >
                                                    <?= e($status) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                        <button type="submit" class="save-status">
                                            <i class="fas fa-check"></i>
                                            Simpan
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </td>


                            <!-- DETAIL -->
                            <td>

                                <a
                                    href="order_detail.php?id=<?= (int)$order['id'] ?>"
                                    class="detail-button"
                                >
                                    Lihat
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

</div>


<style>

/* =========================================
   FARASTORE ADMIN - MODERN ORDERS PAGE
========================================= */

.farastore-orders {
    width: 100%;
    color: #392b3b;
    box-sizing: border-box;
}

.farastore-orders * {
    box-sizing: border-box;
}


/* HEADER */

.orders-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.orders-eyebrow {
    display: block;
    margin-bottom: 8px;
    color: #df6490;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.8px;
}

.orders-heading h1 {
    margin: 0;
    color: #352738;
    font-family: 'Playfair Display', serif;
    font-size: 30px;
}

.orders-heading h1 em {
    color: #df6490;
}

.orders-heading p {
    margin: 8px 0 0;
    color: #978a98;
    font-size: 13px;
}

.orders-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 17px;
    border: 1px solid #f1d5e0;
    border-radius: 11px;
    background: white;
    color: #d95885;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    transition: .2s;
}

.orders-back:hover {
    background: #fff0f5;
    transform: translateY(-2px);
}


/* STATISTICS */

.orders-statistics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.order-stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
    padding: 19px;
    background: #fff;
    border: 1px solid #f2e3e9;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(80, 45, 65, .035);
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 13px;
    font-size: 17px;
}

.stat-icon.pink {
    background: #fff0f5;
    color: #df6490;
}

.stat-icon.orange {
    background: #fff4e7;
    color: #d99445;
}

.stat-icon.purple {
    background: #f1edff;
    color: #8b70cb;
}

.stat-icon.green {
    background: #eaf8f0;
    color: #399b70;
}

.order-stat-card span,
.order-stat-card small {
    display: block;
    color: #998d99;
    font-size: 11px;
}

.order-stat-card strong {
    display: block;
    margin: 4px 0;
    color: #382a3c;
    font-size: 23px;
    line-height: 1.2;
}

.order-stat-card small {
    font-size: 10px;
}


/* PANEL */

.orders-panel {
    padding: 25px;
    background: #fff;
    border: 1px solid #f0e2e9;
    border-radius: 19px;
    box-shadow: 0 8px 30px rgba(80, 45, 65, .035);
}

.orders-panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 22px;
}

.orders-panel-heading h2 {
    margin: 0;
    color: #352738;
    font-family: 'Playfair Display', serif;
    font-size: 22px;
}

.orders-panel-heading p {
    margin: 6px 0 0;
    color: #998d99;
    font-size: 12px;
}

.orders-total-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 30px;
    background: #fff0f5;
    color: #d95885;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}


/* TABLE */

.orders-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #f1e4ea;
    border-radius: 13px;
}

.modern-orders-table {
    width: 100%;
    min-width: 1050px;
    border-collapse: collapse;
    background: #fff;
}

.modern-orders-table thead {
    background: #fff4f7;
}

.modern-orders-table th {
    padding: 16px 13px;
    color: #bd607f;
    text-align: left;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .7px;
    text-transform: uppercase;
    white-space: nowrap;
}

.modern-orders-table td {
    padding: 16px 13px;
    border-top: 1px solid #f3e9ee;
    color: #564858;
    font-size: 12px;
    vertical-align: middle;
}

.modern-orders-table tbody tr {
    transition: background .2s;
}

.modern-orders-table tbody tr:hover {
    background: #fffafd;
}


/* ORDER CODE */

.order-code-cell strong {
    display: block;
    color: #392b3b;
    font-size: 12px;
    white-space: nowrap;
}

.order-code-cell small {
    display: block;
    margin-top: 6px;
    color: #a092a0;
    font-size: 10px;
    white-space: nowrap;
}


/* CUSTOMER */

.customer-cell {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 130px;
}

.customer-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #fce6ee;
    color: #d95885;
    font-size: 12px;
    font-weight: 800;
}

.customer-cell span {
    color: #514253;
    font-weight: 600;
    line-height: 1.5;
}


/* PRODUCTS */

.order-products {
    display: flex;
    align-items: center;
    min-width: 150px;
}

.product-names {
    min-width: 0;
}

.product-names strong {
    display: block;
    max-width: 180px;
    overflow: hidden;
    color: #4a394b;
    font-size: 12px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-names small {
    display: block;
    margin-top: 6px;
    color: #a092a0;
    font-size: 10px;
}

.additional-products {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-top: 8px;
}

.additional-products span {
    color: #887889;
    font-size: 10px;
}


/* PAYMENT */

.payment-cell strong {
    display: block;
    color: #554457;
    font-size: 11px;
}

.payment-cell small {
    display: block;
    margin-top: 5px;
    color: #9b8e9c;
    font-size: 10px;
}


/* PROOF */

.proof-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 11px;
    border-radius: 9px;
    background: #e9f8ef;
    color: #23875b;
    text-decoration: none;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.proof-button:hover {
    background: #d8f1e3;
}

.proof-not-needed {
    color: #9b909d;
    font-size: 11px;
}

.proof-missing {
    color: #c18735;
    font-size: 10px;
    font-weight: 600;
}


/* PRICE */

.order-price {
    color: #d65380 !important;
    font-size: 12px !important;
    font-weight: 800;
    white-space: nowrap;
}


/* STATUS FORM */

.status-form {
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: 7px;
    min-width: 130px;
}

.status-form select {
    width: 100%;
    min-width: 130px;
    padding: 9px 10px;
    border: 1px solid #f0dce5;
    border-radius: 9px;
    outline: none;
    background: white;
    color: #554457;
    font-size: 10px;
    cursor: pointer;
}

.status-form select:focus {
    border-color: #df6490;
}

.status-form select.status-waiting {
    border-color: #f4dfbb;
    background: #fff8e9;
    color: #b57b28;
}

.status-form select.status-shipping {
    border-color: #d8e3fa;
    background: #f1f5ff;
    color: #5477bd;
}

.status-form select.status-cancelled {
    border-color: #f1d2d2;
    background: #fff2f2;
    color: #c65a5a;
}

.status-form select.status-processing {
    border-color: #f2dce7;
    background: #fff6f9;
    color: #c75b83;
}

.save-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 9px 10px;
    border: 0;
    border-radius: 9px;
    background: #df6490;
    color: white;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
}

.save-status:hover {
    background: #c94e7b;
}


/* COMPLETED STATUS */

.completed-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 13px;
    border-radius: 20px;
    background: #e9f8ef;
    color: #23875b;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.completed-info {
    display: block;
    margin-top: 6px;
    color: #99909a;
    font-size: 9px;
    white-space: nowrap;
}


/* DETAIL */

.detail-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 12px;
    border: 1px solid #f2d4e0;
    border-radius: 9px;
    background: #fff7fa;
    color: #d65380;
    text-decoration: none;
    font-size: 10px;
    font-weight: 700;
    transition: .2s;
    white-space: nowrap;
}

.detail-button:hover {
    background: #fce8f0;
    border-color: #e98aaa;
}


/* EMPTY */

.orders-empty {
    padding: 65px 20px;
    text-align: center;
}

.empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 65px;
    height: 65px;
    margin: 0 auto 16px;
    border-radius: 20px;
    background: #fff0f5;
    color: #df6490;
    font-size: 25px;
}

.orders-empty h3 {
    margin: 0 0 8px;
    color: #453548;
    font-size: 17px;
}

.orders-empty p {
    margin: 0;
    color: #a092a0;
    font-size: 12px;
}


/* RESPONSIVE */

@media (max-width: 1100px) {
    .orders-statistics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 650px) {
    .orders-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .orders-heading h1 {
        font-size: 25px;
    }

    .orders-statistics {
        gap: 10px;
    }

    .order-stat-card {
        padding: 13px;
        gap: 9px;
    }

    .stat-icon {
        width: 37px;
        height: 37px;
        font-size: 14px;
    }

    .order-stat-card strong {
        font-size: 19px;
    }

    .orders-panel {
        padding: 15px;
        border-radius: 14px;
    }

    .orders-panel-heading {
        align-items: flex-start;
        flex-direction: column;
    }
}

</style>

<?php admin_footer(); ?>
