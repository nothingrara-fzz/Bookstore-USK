<?php
/**
 * FARASTORE - HALAMAN HOME
 *
 * Fitur:
 * - Menampilkan 5 buku best seller berdasarkan jumlah terjual.
 * - Mencari buku berdasarkan judul, penulis, atau kategori.
 * - Menampilkan sampul buku asli pada bagian hero.
 */

require_once __DIR__ . '/config.php';

$keyword = trim($_GET['q'] ?? '');

/* =========================================
   MENGHITUNG 5 BUKU BEST SELLER OTOMATIS
========================================= */

$bestSellerStatement = $pdo->query("
    SELECT
        oi.book_id,
        SUM(oi.quantity) AS total_sold
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE o.status IN (
        'Pesanan Diproses',
        'Dikemas',
        'Dikirim',
        'Selesai'
    )
    GROUP BY oi.book_id
    ORDER BY total_sold DESC, oi.book_id ASC
    LIMIT 5
");

$bestSellerIds = array_map(
    'intval',
    array_column($bestSellerStatement->fetchAll(PDO::FETCH_ASSOC), 'book_id')
);

/* =========================================
   PENCARIAN DAN TAMPILAN BUKU
========================================= */

if ($keyword !== '') {
    $search = '%' . $keyword . '%';

    $statement = $pdo->prepare("
        SELECT
            b.*,
            COALESCE(sales.total_sold, 0) AS total_sold
        FROM books b
        LEFT JOIN (
            SELECT
                oi.book_id,
                SUM(oi.quantity) AS total_sold
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE o.status IN (
                'Pesanan Diproses',
                'Dikemas',
                'Dikirim',
                'Selesai'
            )
            GROUP BY oi.book_id
        ) sales ON sales.book_id = b.id
        WHERE b.title LIKE ?
           OR b.author LIKE ?
           OR b.category LIKE ?
        ORDER BY total_sold DESC, b.title ASC
    ");

    $statement->execute([$search, $search, $search]);
    $books = $statement->fetchAll(PDO::FETCH_ASSOC);

} else {

    $statement = $pdo->query("
        SELECT
            b.*,
            sales.total_sold
        FROM books b
        INNER JOIN (
            SELECT
                oi.book_id,
                SUM(oi.quantity) AS total_sold
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE o.status IN (
                'Pesanan Diproses',
                'Dikemas',
                'Dikirim',
                'Selesai'
            )
            GROUP BY oi.book_id
            ORDER BY total_sold DESC, oi.book_id ASC
            LIMIT 5
        ) sales ON sales.book_id = b.id
        ORDER BY sales.total_sold DESC, b.id ASC
    ");

    $books = $statement->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/header.php';
?>

<!-- HERO SECTION -->
<section class="hero">

    <div class="hero-copy">
        <span class="eyebrow">A LITTLE JOY IN EVERY PAGE</span>

        <h1>
            Temukan cerita<br>
            <em>favoritmu.</em>
        </h1>

        <a class="btn btn-primary" href="#cari-buku">
            Cari buku favorit <span>↗</span>
        </a>
        
    </div>

    <!-- FOTO BUKU ASLI -->
    <div class="hero-real-books">

        <div class="hero-book-circle"></div>

        <div class="hero-real-book book-left">
            <img
                src="img/perahu-kertas.jpg"
                alt="Sampul buku Perahu Kertas karya Dee Lestari"
            >
        </div>

        <div class="hero-real-book book-right">
            <img
                src="img/tulisan-sastra.jpg"
                alt="Sampul buku Tulisan Sastra karya Tenderlova"
            >
        </div>

    </div>

</section>

<!-- PENCARIAN BUKU -->
<section class="section" id="cari-buku">

    <div class="section-head">
        <div>
            <span class="eyebrow">FIND YOUR NEXT STORY</span>
            <h2>Cari buku <em>favoritmu.</em></h2>
            <p>
                Temukan buku berdasarkan judul, penulis, atau kategori.
            </p>
        </div>
    </div>

    <form class="home-search" method="get" action="index.php">

        <span class="home-search-icon">⌕</span>

        <input
            type="search"
            name="q"
            value="<?= e($keyword) ?>"
            placeholder="Contoh: Laut Bercerita, Tere Liye, Novel..."
            aria-label="Cari judul, penulis, atau kategori buku"
        >

        <button type="submit">Cari buku</button>

        <?php if ($keyword !== ''): ?>
            <a class="home-search-reset" href="index.php">Reset</a>
        <?php endif; ?>

    </form>

    <!-- HASIL BUKU -->
    <div class="section-head home-result-heading">

        <div>
            <span class="eyebrow">
                <?= $keyword !== '' ? 'SEARCH RESULTS' : 'CURATED FOR YOU' ?>
            </span>

            <h2>
                <?php if ($keyword !== ''): ?>
                    Hasil pencarian <em>"<?= e($keyword) ?>"</em>
                <?php else: ?>
                    Best seller <em>pilihan</em>
                <?php endif; ?>
            </h2>

            <p><?= count($books) ?> buku ditemukan.</p>
        </div>

        <?php if ($keyword === ''): ?>
            <a class="text-link" href="books.php">
                Lihat semua buku ↗
            </a>
        <?php endif; ?>

    </div>

    <?php if ($books): ?>

        <div class="book-grid">

            <?php foreach ($books as $book): ?>

                <article class="book-card">

                    <a
                        class="cover-wrap"
                        href="book_detail.php?id=<?= (int) $book['id'] ?>"
                    >

                        <img
                            src="<?= e($book['image']) ?>"
                            alt="<?= e($book['title']) ?>"
                        >

                        <?php if (in_array((int) $book['id'], $bestSellerIds, true)): ?>
                            <span class="tag">BEST SELLER</span>
                        <?php endif; ?>

                    </a>

                    <div class="book-info">

                        <small><?= e($book['category']) ?></small>

                        <h3><?= e($book['title']) ?></h3>

                        <p><?= e($book['author']) ?></p>

                        <div class="book-bottom">

                            <b><?= rupiah($book['price']) ?></b>

                            <?php if (is_logged_in()): ?>

                                <form method="post" action="cart_action.php">

                                    <input
                                        type="hidden"
                                        name="book_id"
                                        value="<?= (int) $book['id'] ?>"
                                    >

                                    <button
                                        class="icon-btn"
                                        type="submit"
                                        title="Tambah ke keranjang"
                                    >＋</button>

                                </form>

                            <?php else: ?>

                                <a
                                    class="icon-btn"
                                    href="login.php"
                                    title="Login untuk membeli buku"
                                >＋</a>

                            <?php endif; ?>

                        </div>
                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="home-no-results">
            <strong>Buku tidak ditemukan.</strong>

            <p>
                Coba gunakan kata kunci judul, nama penulis,
                atau kategori yang berbeda.
            </p>

            <a class="btn" href="index.php">
                Tampilkan best seller
            </a>
        </div>

    <?php endif; ?>

</section>

<style>
/* =================================
   FARASTORE - HERO BUKU ASLI
================================= */

.hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 35px;
    min-height: 590px;
    padding: 65px 7%;
    overflow: hidden;
    background: linear-gradient(120deg, #fff3f6, #fffaf7);
    border-radius: 0 0 35px 35px;
}

.hero-copy {
    position: relative;
    z-index: 2;
}

.hero-copy .eyebrow {
    color: #df6890;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 3px;
}

.hero-copy h1 {
    margin: 25px 0 20px;
    color: #30252c;
    font-family: 'Playfair Display', serif;
    font-size: clamp(45px, 5.5vw, 76px);
    line-height: 1.12;
}

.hero-copy h1 em {
    color: #df6890;
}

.hero-copy > p {
    max-width: 540px;
    color: #8d7d88;
    font-size: 17px;
    line-height: 1.9;
}

.hero-copy .btn {
    display: inline-flex;
    align-items: center;
    gap: 20px;
    margin-top: 22px;
    padding: 17px 27px;
    border-radius: 15px;
    background: #df6890;
    color: white;
    text-decoration: none;
    font-weight: 700;
    box-shadow: 0 12px 25px rgba(223, 104, 144, .2);
    transition: .25s;
}

.hero-copy .btn:hover {
    background: #cb507b;
    transform: translateY(-3px);
}

.hero-note {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 35px;
    color: #9b8792;
    font-size: 13px;
}

.hero-note .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #91b59e;
}

/* AREA FOTO BUKU */

.hero-real-books {
    position: relative;
    min-height: 440px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-book-circle {
    position: absolute;
    width: 370px;
    height: 370px;
    border-radius: 50%;
    background: #f8dce6;
}

/* SAMPUL BUKU */

.hero-real-book {
    position: absolute;
    width: 215px;
    height: 335px;
    padding: 0;
    overflow: hidden;
    border-radius: 9px;
    background: white;
    box-shadow: 0 22px 45px rgba(70, 35, 52, .2);
    transition: .3s ease;
}

.hero-real-book img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-left {
    z-index: 1;
    left: 13%;
    transform: rotate(-9deg);
}

.book-right {
    z-index: 2;
    right: 12%;
    transform: rotate(8deg);
}

.hero-real-book:hover {
    z-index: 5;
    transform: rotate(0) translateY(-10px) scale(1.04);
}

/* STIKER */

.hero-read-sticker {
    position: absolute;
    z-index: 3;
    right: 3%;
    bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 105px;
    height: 105px;
    border-radius: 50%;
    background: #fffaf3;
    color: #df6890;
    font-size: 15px;
    font-weight: 800;
    line-height: 1.5;
    text-align: center;
    transform: rotate(10deg);
    box-shadow: 0 10px 25px rgba(70, 35, 52, .08);
}

/* RESPONSIVE */

@media (max-width: 900px) {
    .hero {
        grid-template-columns: 1fr;
        padding: 55px 7%;
        gap: 15px;
    }

    .hero-copy {
        text-align: center;
    }

    .hero-copy > p {
        margin-left: auto;
        margin-right: auto;
    }

    .hero-note {
        justify-content: center;
    }

    .hero-real-books {
        min-height: 390px;
    }

    .hero-real-book {
        width: 180px;
        height: 280px;
    }

    .hero-book-circle {
        width: 300px;
        height: 300px;
    }
}

@media (max-width: 520px) {
    .hero {
        padding: 45px 20px 30px;
    }

    .hero-copy h1 {
        font-size: 43px;
    }

    .hero-copy > p {
        font-size: 14px;
    }

    .hero-real-books {
        min-height: 320px;
    }

    .hero-real-book {
        width: 145px;
        height: 225px;
    }

    .book-left {
        left: 8%;
    }

    .book-right {
        right: 8%;
    }

    .hero-book-circle {
        width: 240px;
        height: 240px;
    }

    .hero-read-sticker {
        width: 75px;
        height: 75px;
        right: 0;
        bottom: 5px;
        font-size: 11px;
    }
}
</style>

<?php include __DIR__ . '/footer.php'; ?>