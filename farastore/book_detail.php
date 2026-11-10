<?php
require_once 'config.php';

$s = $pdo->prepare("
    SELECT *
    FROM books
    WHERE id = ?
");

$s->execute([(int)($_GET['id'] ?? 0)]);
$b = $s->fetch();

if (!$b) {
    redirect('books.php');
}

$stock = (int)$b['stock'];

include 'header.php';
?>

<div class="detail-layout">

    <!-- COVER BUKU -->
    <div class="detail-cover">
        <img
            src="<?= e($b['image']) ?>"
            alt="<?= e($b['title']) ?>"
        >

        <?php if ($stock <= 0): ?>
            <span class="detail-stock-overlay">STOK HABIS</span>
        <?php endif; ?>
    </div>

    <!-- INFORMASI BUKU -->
    <div class="detail-copy">

        <span class="eyebrow">
            <?= e($b['category']) ?>
        </span>

        <h1><?= e($b['title']) ?></h1>

        <div class="muted">
            oleh <?= e($b['author']) ?>
        </div>

        <div class="price">
            <?= rupiah($b['price']) ?>
        </div>

        <!-- INFORMASI STOK -->
        <?php if ($stock > 0): ?>

            <div class="detail-stock available">
                <span class="stock-dot"></span>

                Stok tersedia:
                <strong><?= $stock ?> buku</strong>
            </div>

        <?php else: ?>

            <div class="detail-stock empty-stock">
                <span class="stock-dot"></span>

                <strong>STOK HABIS</strong>

                <span>Buku ini sementara tidak tersedia.</span>
            </div>

        <?php endif; ?>

        <!-- SINOPSIS BUKU -->
        <h3>Tentang buku ini</h3>

        <p>
            <?= nl2br(e($b['synopsis'])) ?>
        </p>

        <!-- PILIHAN PEMBELIAN -->
        <?php if ($stock > 0): ?>

            <form
                method="post"
                action="cart_action.php"
                class="detail-cart-form"
            >

                <input
                    type="hidden"
                    name="book_id"
                    value="<?= (int)$b['id'] ?>"
                >

                <label for="quantity">
                    Jumlah pembelian
                </label>

                <input
                    class="qty"
                    id="quantity"
                    type="number"
                    name="quantity"
                    value="1"
                    min="1"
                    max="<?= $stock ?>"
                    required
                >

                <small class="quantity-note">
                    Maksimal pembelian <?= $stock ?> buku.
                </small>

                <div class="detail-buttons">

                    <!-- TAMBAH KE KERANJANG -->
                    <button
                        class="btn"
                        type="submit"
                        name="action"
                        value="add"
                    >
                        ＋ Tambahkan ke keranjang
                    </button>

                    <!-- PESAN SEKARANG -->
                    <button
                        class="btn btn-order-now"
                        type="submit"
                        formaction="checkout.php"
                        formmethod="get"
                        name="buy_now"
                        value="1"
                    >
                        Pesan Sekarang
                    </button>

                </div>

            </form>

        <?php else: ?>

            <button
                class="btn stock-button-disabled"
                type="button"
                disabled
            >
                Stok Habis
            </button>

        <?php endif; ?>

    </div>

</div>

<style>

/* COVER DETAIL */
.detail-cover {
    position: relative;
}

/* LABEL STOK HABIS */
.detail-stock-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(30, 20, 25, .55);
    color: white;
    font-size: 17px;
    font-weight: 800;
    letter-spacing: 2px;
}

/* INFORMASI STOK */
.detail-stock {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin: 20px 0;
    padding: 13px 16px;
    border-radius: 10px;
    font-size: 14px;
}

.detail-stock.available {
    background: #eaf8ef;
    color: #16834b;
}

.detail-stock.empty-stock {
    background: #fff0f0;
    color: #dc3545;
}

.stock-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}

/* FORM PEMBELIAN */
.detail-cart-form {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    margin-top: 22px;
}

.detail-cart-form label {
    font-size: 14px;
    font-weight: 600;
}

.quantity-note {
    color: #888;
    font-size: 12px;
}

/* TOMBOL PEMBELIAN */
.detail-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 8px;
}

.detail-buttons .btn {
    margin-top: 0;
}

.btn-order-now {
    background: #dc3d68;
    color: white;
}

.btn-order-now:hover {
    background: #c92e59;
}

/* TOMBOL STOK HABIS */
.stock-button-disabled,
.stock-button-disabled:disabled {
    margin-top: 20px;
    background: #bdbdbd;
    color: white;
    cursor: not-allowed;
    opacity: .8;
}

</style>

<?php include 'footer.php'; ?>