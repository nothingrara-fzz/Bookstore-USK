
<?php
require_once 'config.php';
require_login();

$s = $pdo->prepare("
    SELECT
        c.*,
        b.title,
        b.author,
        b.price,
        b.image,
        b.stock
    FROM cart_items c
    JOIN books b ON b.id = c.book_id
    WHERE c.user_id = ?
");

$s->execute([$_SESSION['user_id']]);
$items = $s->fetchAll();

$total = 0;
$hasStockProblem = false;

foreach ($items as $it) {
    $total += $it['price'] * $it['quantity'];

    if ((int)$it['stock'] <= 0 || (int)$it['quantity'] > (int)$it['stock']) {
        $hasStockProblem = true;
    }
}

include 'header.php';
?>

<div class="page-heading">
    <span class="eyebrow">SAVED FOR LATER</span>
    <h1>Keranjang <em>belanja.</em></h1>
    <p>Semua cerita yang ingin kamu bawa pulang.</p>
</div>

<?php if (!$items): ?>

    <div class="empty">
        <b>Keranjangmu masih kosong ♡</b>
        Yuk, temukan buku yang ingin kamu baca.
        <br><br>
        <a class="btn" href="books.php">Jelajahi buku</a>
    </div>

<?php else: ?>

    <div class="panel">

        <?php foreach ($items as $it): ?>

            <?php
            $stock = (int)$it['stock'];
            $quantity = (int)$it['quantity'];
            $stockProblem = ($stock <= 0 || $quantity > $stock);
            ?>

            <div class="cart-row">

                <img
                    src="<?= e($it['image']) ?>"
                    alt="<?= e($it['title']) ?>"
                >

                <div class="cart-item-info">

                    <h3><?= e($it['title']) ?></h3>

                    <div class="muted">
                        <?= e($it['author']) ?>
                        ·
                        <?= rupiah($it['price']) ?>
                    </div>

                    <?php if ($stock > 0): ?>
                        <div class="cart-stock available">
                            Stok tersedia:
                            <strong><?= $stock ?> buku</strong>
                        </div>
                    <?php else: ?>
                        <div class="cart-stock unavailable">
                            STOK HABIS
                        </div>
                    <?php endif; ?>

                    <?php if ($stockProblem): ?>
                        <p class="stock-warning">
                            <?php if ($stock <= 0): ?>
                                Buku ini sudah habis. Silakan hapus dari keranjang.
                            <?php else: ?>
                                Jumlah pembelian melebihi stok.
                                Silakan perbarui jumlahnya.
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <form
                        method="post"
                        action="cart_update.php"
                        class="inline-form cart-actions"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$it['book_id'] ?>"
                        >

                        <input
                            class="qty"
                            style="width:75px;padding:9px"
                            type="number"
                            name="quantity"
                            min="1"
                            max="<?= max(1, $stock) ?>"
                            value="<?= max(1, min($quantity, max(1, $stock))) ?>"
                            <?= $stock <= 0 ? 'disabled' : '' ?>
                            required
                        >

                        <button
                            type="submit"
                            name="update"
                            value="1"
                            class="update-btn"
                            <?= $stock <= 0 ? 'disabled' : '' ?>
                        >
                            Update
                        </button>

                        <button
                            type="submit"
                            name="delete"
                            value="1"
                            class="delete-btn"
                            formnovalidate
                        >
                            Hapus
                        </button>

                    </form>

                </div>

                <b class="item-subtotal">
                    <?= rupiah($it['price'] * $it['quantity']) ?>
                </b>

            </div>

        <?php endforeach; ?>

        <div class="cart-total">
            <span>Total belanja</span>
            <b><?= rupiah($total) ?></b>
        </div>

        <?php if ($hasStockProblem): ?>

            <button class="btn full checkout-disabled" type="button" disabled>
                Periksa stok buku terlebih dahulu
            </button>

        <?php else: ?>

            <a class="btn full" href="checkout.php">
                Lanjut ke checkout ↗
            </a>

        <?php endif; ?>

    </div>

<?php endif; ?>

<style>
.cart-stock {
    margin-top: 8px;
    font-size: 13px;
}

.cart-stock.available {
    color: #16834b;
}

.cart-stock.unavailable {
    color: #dc3545;
    font-weight: 700;
}

.stock-warning {
    margin-top: 7px;
    color: #dc3545;
    font-size: 12px;
}

.cart-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 12px;
}

.update-btn,
.delete-btn {
    padding: 9px 14px;
    border-radius: 9px;
    cursor: pointer;
    font-weight: 600;
}

.update-btn {
    border: 1px solid #ed4f78;
    background: #ed4f78;
    color: white;
}

.update-btn:hover {
    background: #dc3d68;
}

.delete-btn {
    border: 1px solid #ed4f78;
    background: white;
    color: #ed4f78;
}

.delete-btn:hover {
    background: #fff0f5;
}

.checkout-disabled,
.checkout-disabled:disabled {
    background: #bdbdbd;
    color: white;
    cursor: not-allowed;
    opacity: .8;
}

@media(max-width:600px) {
    .cart-row {
        flex-wrap: wrap;
    }

    .item-subtotal {
        margin-left: auto;
    }
}
</style>

<?php include 'footer.php'; ?>
