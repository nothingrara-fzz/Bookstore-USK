<?php
/**
 * FARASTORE - DASHBOARD ADMIN
 * Ringkasan pengguna, buku, pesanan, omzet, dan pesanan terbaru.
 */

require_once __DIR__ . '/_layout.php';

// Ringkasan data.
$totalUsers = (int) $pdo
    ->query('SELECT COUNT(*) FROM users')
    ->fetchColumn();

$totalBooks = (int) $pdo
    ->query('SELECT COUNT(*) FROM books')
    ->fetchColumn();

$totalOrders = (int) $pdo
    ->query('SELECT COUNT(*) FROM orders')
    ->fetchColumn();

// Omzet pesanan yang sedang diproses sampai selesai.
$totalRevenue = (float) $pdo->query("
    SELECT COALESCE(SUM(total), 0)
    FROM orders
    WHERE status IN (
        'Pesanan Diproses',
        'Dikemas',
        'Dikirim',
        'Selesai'
    )
")->fetchColumn();

// Tujuh pesanan terbaru.
$recentOrders = $pdo->query("
    SELECT
        o.id,
        o.order_code,
        o.total,
        o.status,
        o.created_at,
        u.name AS customer_name
    FROM orders AS o
    INNER JOIN users AS u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 7
")->fetchAll();

admin_header('Dashboard');
?>

<section class="stats">
    <article class="stat">
        <small>Total pengguna</small>
        <strong><?= number_format($totalUsers, 0, ',', '.') ?></strong>
    </article>

    <article class="stat">
        <small>Total buku</small>
        <strong><?= number_format($totalBooks, 0, ',', '.') ?></strong>
    </article>

    <article class="stat">
        <small>Total pesanan</small>
        <strong><?= number_format($totalOrders, 0, ',', '.') ?></strong>
    </article>

    <article class="stat">
        <small>Omzet pesanan aktif/selesai</small>
        <strong><?= rupiah($totalRevenue) ?></strong>
    </article>
</section>

<section class="panel">
    <h2>Pesanan terbaru</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode pesanan</th>
                    <th>Nama pengguna</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$recentOrders): ?>
                    <tr>
                        <td colspan="6">Belum ada pesanan yang masuk.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><?= e($order['order_code']) ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                            <td><?= rupiah($order['total']) ?></td>
                            <td><?= e($order['status']) ?></td>
                            <td>
                                <a
                                    class="admin-link"
                                    href="order_detail.php?id=<?= (int) $order['id'] ?>"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php admin_footer(); ?>
