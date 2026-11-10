
<?php
/**
 * FARASTORE - LAPORAN OMZET
 */

require_once __DIR__ . '/_layout.php';

// ==========================================
// 1. STATUS PESANAN YANG DIHITUNG SEBAGAI OMZET
// ==========================================

// Pesanan yang sudah dikonfirmasi dan tidak dibatalkan.
$validStatuses = [
    'Pesanan Diproses',
    'Dikemas',
    'Dikirim',
    'Selesai'
];

$validStatusesSql = "'" . implode("', '", $validStatuses) . "'";

// ==========================================
// 2. TOTAL OMZET PESANAN VALID
// ==========================================

$totalRevenue = (float) $pdo->query("
    SELECT COALESCE(SUM(total), 0)
    FROM orders
    WHERE TRIM(status) IN ($validStatusesSql)
")->fetchColumn();

// ==========================================
// 3. OMZET PESANAN YANG SUDAH DIKEMAS
// ==========================================

$processedRevenue = (float) $pdo->query("
    SELECT COALESCE(SUM(total), 0)
    FROM orders
    WHERE TRIM(status) IN (
        'Dikemas',
        'Dikirim',
        'Selesai'
    )
")->fetchColumn();

// ==========================================
// 4. REKAP TRANSAKSI HARIAN
// ==========================================

$dailyReports = $pdo->query("
    SELECT
        DATE(created_at) AS report_date,
        COUNT(*) AS order_count,
        COALESCE(SUM(total), 0) AS revenue
    FROM orders
    WHERE TRIM(status) IN ($validStatusesSql)
    GROUP BY DATE(created_at)
    ORDER BY report_date DESC
")->fetchAll();

// ==========================================
// 5. JUMLAH HARI TRANSAKSI
// ==========================================

$totalDays = count($dailyReports);

admin_header('Laporan omzet');
?>

<section class="stats">

    <article class="stat">
        <small>Total nilai pesanan terkonfirmasi</small>
        <strong><?= rupiah($totalRevenue) ?></strong>
    </article>

    <article class="stat">
        <small>Pesanan dikemas/dikirim/selesai</small>
        <strong><?= rupiah($processedRevenue) ?></strong>
    </article>

    <article class="stat">
        <small>Hari transaksi</small>
        <strong><?= $totalDays ?></strong>
    </article>

</section>

<section class="panel">

    <h2>Rekap transaksi harian</h2>

    <p class="muted">
        Omzet dihitung berdasarkan pesanan yang telah dikonfirmasi
        admin, diproses, dikemas, dikirim, atau selesai.
        Pesanan menunggu pembayaran, menunggu verifikasi,
        dan dibatalkan tidak masuk dalam perhitungan.
        Nilai COD merupakan nilai pesanan, bukan bukti uang telah diterima.
    </p>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah pesanan</th>
                    <th>Omzet</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!$dailyReports): ?>

                    <tr>
                        <td colspan="3">
                            Belum ada transaksi.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($dailyReports as $report): ?>

                        <tr>

                            <td>
                                <?= date(
                                    'd M Y',
                                    strtotime($report['report_date'])
                                ) ?>
                            </td>

                            <td>
                                <?= (int) $report['order_count'] ?>
                            </td>

                            <td>
                                <strong>
                                    <?= rupiah($report['revenue']) ?>
                                </strong>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>

<?php admin_footer(); ?>
