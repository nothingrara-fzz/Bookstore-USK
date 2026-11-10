
<?php
require_once 'config.php';

// Login tidak diwajibkan untuk melihat koleksi buku.
$term = trim($_GET['q'] ?? '');

if ($term) {
    $s = $pdo->prepare("
        SELECT *
        FROM books
        WHERE title LIKE ?
           OR author LIKE ?
           OR category LIKE ?
        ORDER BY title
    ");

    $like = "%$term%";
    $s->execute([$like, $like, $like]);
    $books = $s->fetchAll();

} else {

    $books = $pdo->query("
        SELECT *
        FROM books
        ORDER BY created_at DESC
    ")->fetchAll();
}

include 'header.php';
?>

<div class="page-heading">
    <span class="eyebrow">THE FARASTORE COLLECTION</span>

    <h1>Semua buku.</h1>
</div>

<div class="toolbar">

    <span class="muted">
        <?= count($books) ?> buku tersedia
    </span>

    <form class="search" method="get">
        <input
            name="q"
            value="<?= e($term) ?>"
            placeholder="Cari judul, penulis, kategori..."
        >

        <button type="submit">⌕</button>
    </form>

</div>

<div class="book-grid">

    <?php foreach ($books as $b): ?>

        <?php $stock = (int)$b['stock']; ?>

        <article class="book-card">

            <!-- COVER BUKU -->
            <a
                class="cover-wrap"
                href="book_detail.php?id=<?= (int)$b['id'] ?>"
            >

                <img
                    src="<?= e($b['image']) ?>"
                    alt="<?= e($b['title']) ?>"
                >

                <?php if ($b['bestseller']): ?>
                    <span class="tag">BEST SELLER</span>
                <?php endif; ?>

                <?php if ($stock <= 0): ?>
                    <span class="stock-overlay">STOK HABIS</span>
                <?php endif; ?>

            </a>

            <!-- INFORMASI BUKU -->
            <div class="book-info">

                <small><?= e($b['category']) ?></small>

                <h3><?= e($b['title']) ?></h3>

                <p><?= e($b['author']) ?></p>

                <!-- INFORMASI STOK -->
                <?php if ($stock > 0): ?>

                    <div class="stock-available">
                        <span class="stock-dot"></span>
                        Stok tersedia: <b><?= $stock ?></b>
                    </div>

                <?php else: ?>

                    <div class="stock-empty">
                        <span class="stock-dot"></span>
                        Stok habis
                    </div>

                <?php endif; ?>

                <!-- HARGA DAN PILIHAN PEMESANAN -->
                <div class="book-bottom">

                    <b class="book-price">
                        <?= rupiah($b['price']) ?>
                    </b>

                    <?php if ($stock > 0): ?>

                        <div class="book-actions">

                            <?php if (is_logged_in()): ?>

                                <!-- TOMBOL TAMBAH KE KERANJANG -->
                                <form method="post" action="cart_action.php">

                                    <input
                                        type="hidden"
                                        name="book_id"
                                        value="<?= (int)$b['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="icon-btn cart-add-btn"
                                        title="Tambah ke keranjang"
                                    >
                                        ＋
                                    </button>

                                </form>

                                <!-- TOMBOL PESAN LANGSUNG -->
                                <a
                                    href="checkout.php?book_id=<?= (int)$b['id'] ?>"
                                    class="icon-btn buy-now-btn"
                                    title="Pesan langsung"
                                >
                                    Pesan
                                </a>

                            <?php else: ?>

                                <a
                                    href="login.php"
                                    class="icon-btn cart-add-btn"
                                    title="Login untuk menambahkan ke keranjang"
                                >
                                    ＋
                                </a>

                                <a
                                    href="login.php"
                                    class="icon-btn buy-now-btn"
                                    title="Login untuk memesan langsung"
                                >
                                    Pesan
                                </a>

                            <?php endif; ?>

                        </div>

                    <?php else: ?>

                        <button
                            type="button"
                            class="icon-btn stock-disabled"
                            disabled
                            title="Stok buku habis"
                        >
                            ×
                        </button>

                    <?php endif; ?>

                </div>

            </div>

        </article>

    <?php endforeach; ?>

</div>

<?php if (!$books): ?>

    <div class="empty">
        <b>Buku belum ditemukan</b>
        Coba kata kunci yang berbeda.
    </div>

<?php endif; ?>

<style>

/* INFORMASI STOK TERSEDIA */
.stock-available {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 12px;
    font-size: 12px;
    color: #16834b;
}

.stock-available b {
    font-weight: 700;
}

.stock-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    background: #20a464;
}

/* STOK HABIS */
.stock-empty {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 12px;
    font-size: 12px;
    font-weight: 700;
    color: #dc3d50;
}

.stock-empty .stock-dot {
    background: #dc3d50;
}

/* LABEL STOK HABIS DI COVER */
.cover-wrap {
    position: relative;
}

.stock-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(30, 20, 25, .55);
    color: white;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 2px;
}

/* TOMBOL KETIKA STOK HABIS */
.icon-btn.stock-disabled,
.icon-btn.stock-disabled:disabled {
    background: #e5e5e5;
    color: #999;
    cursor: not-allowed;
    opacity: .8;
    border: none;
}

/* =====================================
   PERBAIKAN TOMBOL KERANJANG DAN PESAN
===================================== */

.book-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    width: 100%;
    box-sizing: border-box;
}

.book-price {
    white-space: nowrap;
    flex-shrink: 0;
}

/* PEMBUNGKUS TOMBOL */
.book-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-shrink: 0;
}

.book-actions form {
    margin: 0;
    padding: 0;
    flex-shrink: 0;
}

/* TOMBOL TAMBAH KERANJANG */
.book-card .book-bottom .cart-add-btn {
    width: 36px !important;
    min-width: 36px !important;
    max-width: 36px !important;
    height: 36px !important;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    flex-shrink: 0;
    text-decoration: none;
}

/* TOMBOL PESAN LANGSUNG */
.book-card .book-bottom .buy-now-btn {
    width: 62px !important;
    min-width: 62px !important;
    max-width: 62px !important;
    height: 36px !important;
    padding: 0 8px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: visible;
    text-decoration: none;
    font-size: 12px !important;
    line-height: 1;
}

/* RESPONSIVE */
@media (max-width: 400px) {
    .book-bottom {
        gap: 5px;
    }

    .book-actions {
        gap: 5px;
    }

    .book-card .book-bottom .buy-now-btn {
        width: 58px !important;
        min-width: 58px !important;
        max-width: 58px !important;
    }
}

</style>

<?php include 'footer.php'; ?>
