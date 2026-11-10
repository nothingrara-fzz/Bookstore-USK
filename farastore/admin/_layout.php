
<?php
/**
 * FARASTORE - LAYOUT ADMIN
 *
 * File ini menyediakan sidebar, judul halaman, dan penutup halaman.
 * Semua halaman admin selain login memanggil file ini.
 */

require_once __DIR__ . '/../config.php';
require_admin();

/**
 * Menampilkan bagian atas halaman dan navigasi admin.
 */
function admin_header($title)
{
    $menu = [
        'index.php'    => 'Dashboard',
        'users.php'    => 'Data pengguna',
        'orders.php'   => 'Pesanan',
        'books.php'    => 'Kelola buku',
        'messages.php' => 'Pesan Masuk',
    ];

    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | Farastore Admin</title>
        <link rel="stylesheet" href="../assets/style.css">
        <link rel="stylesheet" href="admin-modern.css">
    </head>
    <body class="admin-body">
        <div class="admin-layout">
            <aside class="admin-side">
                <a class="brand" href="index.php">
                    <span>
                        fara<span>store</span>
                        <small>ADMIN PANEL</small>
                    </span>
                </a>

                <nav>
                    <?php foreach ($menu as $file => $label): ?>
                        <a
                            href="<?= e($file) ?>"
                            class="<?= $currentPage === $file || ($file === 'messages.php' && $currentPage === 'message_detail.php') ? 'active' : '' ?>"
                        >
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>

                    <a href="logout.php">Keluar</a>
                </nav>
            </aside>

            <main class="admin-main">
                <header class="admin-top">
                    <h1><?= e($title) ?></h1>
                    <a class="text-link" href="../index.php" target="_blank">
                        Lihat toko ↗
                    </a>
                </header>
    <?php
}

/**
 * Menutup elemen halaman admin.
 */
function admin_footer()
{
    ?>
            </main>
        </div>
    </body>
    </html>
    <?php
}
