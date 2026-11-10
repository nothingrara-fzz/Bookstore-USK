<?php
require_once 'config.php';
require_login();

$u = current_user();

/*
|--------------------------------------------------------------------------
| MODE PESAN LANGSUNG
|--------------------------------------------------------------------------
| Mendukung:
| checkout.php?book_id=ID
| checkout.php?buy_now=1&book_id=ID&quantity=JUMLAH
*/

$buyNow = (
    isset($_GET['book_id']) ||
    (isset($_GET['buy_now']) && $_GET['buy_now'] == '1')
);

$bookId = (int)($_GET['book_id'] ?? 0);
$quantity = (int)($_GET['quantity'] ?? 1);

if ($quantity < 1) {
    $quantity = 1;
}

if ($buyNow) {

    $s = $pdo->prepare("
        SELECT *
        FROM books
        WHERE id = ?
    ");

    $s->execute([$bookId]);
    $book = $s->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        flash('error', 'Buku tidak ditemukan.');
        redirect('books.php');
    }

    if ((int)$book['stock'] <= 0) {
        flash('error', 'Stok buku sudah habis.');
        redirect('book_detail.php?id=' . $bookId);
    }

    if ($quantity > (int)$book['stock']) {
        flash('error', 'Jumlah pembelian melebihi stok yang tersedia.');
        redirect('book_detail.php?id=' . $bookId);
    }

    $book['quantity'] = $quantity;
    $items = [$book];

} else {

    /*
    |--------------------------------------------------------------------------
    | MODE CHECKOUT KERANJANG
    |--------------------------------------------------------------------------
    */

    $s = $pdo->prepare("
        SELECT c.quantity, b.*
        FROM cart_items c
        JOIN books b ON b.id = c.book_id
        WHERE c.user_id = ?
    ");

    $s->execute([$_SESSION['user_id']]);
    $items = $s->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        flash('error', 'Keranjang masih kosong.');
        redirect('cart.php');
    }

}

/*
|--------------------------------------------------------------------------
| HITUNG TOTAL
|--------------------------------------------------------------------------
*/

$total = 0;

foreach ($items as $it) {
    $total += $it['price'] * $it['quantity'];
}

include 'header.php';
?>

<div class="page-heading">
    <span class="eyebrow">ALMOST YOURS</span>
    <h1>Checkout <em>pesanan.</em></h1>
    <p>Pastikan detail pengiriman dan pembayaran sudah benar.</p>
</div>

<form method="post" action="place_order.php" enctype="multipart/form-data">

    <!-- DATA UNTUK MODE PESAN LANGSUNG -->
    <?php if ($buyNow): ?>
        <input type="hidden" name="direct_book_id" value="<?= (int)$book['id'] ?>">
        <input type="hidden" name="quantity" value="<?= (int)$book['quantity'] ?>">
    <?php endif; ?>

    <div class="checkout-grid">

        <div class="form-card">

            <h2 style="font:600 23px 'Playfair Display'">
                Informasi penerima
            </h2>

            <div class="field">
                <label>Nama penerima</label>
                <input
                    type="text"
                    name="name"
                    required
                    value="<?= e($u['name']) ?>"
                >
            </div>

            <div class="field">
                <label>Nomor telepon</label>
                <input
                    type="text"
                    name="phone"
                    required
                    value="<?= e($u['phone']) ?>"
                >
            </div>

            <div class="field">
                <label>Alamat pengiriman</label>
                <textarea name="address" required><?= e($u['address']) ?></textarea>
            </div>

            <h2 style="font:600 23px 'Playfair Display';margin-top:28px">
                Metode pembayaran
            </h2>

            <!-- COD -->
            <label class="radio-card payment-option">
                <input
                    type="radio"
                    name="payment"
                    value="COD"
                    checked
                >

                <span>
                    <b>Bayar di tempat (COD)</b>
                    <small>Bayar saat pesanan diterima.</small>
                </span>
            </label>

            <!-- TRANSFER BANK -->
            <label class="radio-card payment-option">
                <input
                    type="radio"
                    name="payment"
                    value="Transfer"
                >

                <span>
                    <b>Transfer Bank SeaBank</b>
                    <small>Transfer melalui rekening SeaBank.</small>
                </span>
            </label>

            <!-- INFORMASI TRANSFER -->
            <div class="payment-detail" id="transferDetail">

                <div class="bank-box">
                    <div class="payment-logo">
                        <i class="fas fa-university"></i>
                        <b>SeaBank</b>
                    </div>

                    <small>NOMOR REKENING</small>

                    <h2 class="account-number">
                        9014 7191 5040
                    </h2>

                    <div class="account-name">
                        <small>Nama pemilik rekening</small>
                        <b>A/N Aurora Eka Marpaung</b>
                    </div>

                    <p>
                        Silakan transfer sesuai total pembayaran
                        pesanan kamu ke rekening SeaBank di atas.
                    </p>
                </div>

            </div>

            <!-- QRIS -->
            <label class="radio-card payment-option">
                <input
                    type="radio"
                    name="payment"
                    value="QRIS"
                >

                <span>
                    <b>QRIS</b>
                    <small>Bayar menggunakan aplikasi pembayaran yang mendukung QRIS.</small>
                </span>
            </label>

            <!-- INFORMASI QRIS -->
            <div class="payment-detail" id="qrisDetail">

                <div class="bank-box qris-box">

                    <h3>QRIS FARASTORE</h3>

                    <p>
                        Scan kode QR berikut untuk melakukan pembayaran.
                    </p>

                    <img
                        src="img/qris.png"
                        alt="QRIS Pembayaran Farastore"
                        class="qris-image"
                    >

                    <p class="qris-note">
                        Pastikan nama merchant dan jumlah pembayaran
                        sudah sesuai sebelum menyelesaikan transaksi.
                    </p>

                </div>

            </div>

            <!-- UPLOAD BUKTI -->
            <div class="field" id="proofField" style="display:none">

                <label>Upload bukti pembayaran</label>

                <input
                    type="file"
                    name="proof"
                    id="proofInput"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    Wajib untuk Transfer dan QRIS.
                    Format JPG, PNG, WebP. Maksimal 3 MB.
                </small>

            </div>

        </div>

        <!-- RINGKASAN BELANJA -->
        <aside class="form-card">

            <h2 style="font:600 23px 'Playfair Display'">
                Ringkasan belanja
            </h2>

            <?php foreach ($items as $it): ?>

                <div class="summary-line">

                    <span>
                        <?= e($it['title']) ?>
                        ×
                        <?= (int)$it['quantity'] ?>
                    </span>

                    <b>
                        <?= rupiah($it['price'] * $it['quantity']) ?>
                    </b>

                </div>

            <?php endforeach; ?>

            <div class="summary-line">
                <span>Pengiriman</span>
                <b>Gratis</b>
            </div>

            <div class="summary-line total">
                <span>Total</span>
                <b><?= rupiah($total) ?></b>
            </div>

            <button class="btn full" type="submit">
                Buat pesanan ↗
            </button>

            <p class="muted" style="line-height:1.7;margin-top:14px">
                Dengan melanjutkan, kamu menyetujui detail pesanan
                dan metode pembayaran yang dipilih.
            </p>

        </aside>

    </div>

</form>

<style>

/* PILIHAN METODE PEMBAYARAN */

.payment-option {
    cursor: pointer;
    margin-bottom: 12px;
}

/* INFORMASI PEMBAYARAN */

.payment-detail {
    display: none;
    margin: 5px 0 20px;
    animation: paymentFade .25s ease;
}

.payment-detail.show {
    display: block;
}

@keyframes paymentFade {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* KARTU REKENING */

.bank-box {
    padding: 22px;
    border: 1px solid #f5c7d5;
    border-radius: 15px;
    background: #fff7fa;
}

.payment-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #dc3d68;
    font-size: 18px;
    margin-bottom: 20px;
}

.account-number {
    font-size: 27px;
    letter-spacing: 1px;
    color: #dc3d68;
    margin: 8px 0 15px;
}

.account-name {
    padding: 12px;
    background: white;
    border-radius: 9px;
    margin-bottom: 12px;
}

.account-name small {
    display: block;
    margin-bottom: 5px;
    color: #777;
}

.bank-box p {
    font-size: 13px;
    line-height: 1.7;
    color: #777;
}

/* QRIS */

.qris-box {
    text-align: center;
}

.qris-box h3 {
    margin-top: 0;
    color: #dc3d68;
}

.qris-image {
    display: block;
    width: 100%;
    max-width: 350px;
    height: auto;
    object-fit: contain;
    margin: 18px auto;
    padding: 10px;
    background: white;
    border: 1px solid #f5c7d5;
    border-radius: 12px;
}

.qris-note {
    font-size: 12px;
    line-height: 1.7;
    color: #777;
}

/* UPLOAD BUKTI */

#proofField {
    margin-top: 20px;
    padding: 17px;
    background: #fff7fa;
    border: 1px dashed #ed4f78;
    border-radius: 12px;
}

#proofField small {
    display: block;
    margin-top: 8px;
    color: #777;
}

</style>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const paymentRadios = document.querySelectorAll(
        'input[name="payment"]'
    );

    const transferDetail = document.getElementById('transferDetail');
    const qrisDetail = document.getElementById('qrisDetail');

    const proofField = document.getElementById('proofField');
    const proofInput = document.getElementById('proofInput');

    function updatePayment() {

        const selected = document.querySelector(
            'input[name="payment"]:checked'
        );

        const payment = selected ? selected.value : 'COD';

        transferDetail.classList.remove('show');
        qrisDetail.classList.remove('show');

        proofField.style.display = 'none';
        proofInput.required = false;

        if (payment === 'Transfer') {

            transferDetail.classList.add('show');

            proofField.style.display = 'block';
            proofInput.required = true;

        } else if (payment === 'QRIS') {

            qrisDetail.classList.add('show');

            proofField.style.display = 'block';
            proofInput.required = true;

        }

    }

    paymentRadios.forEach(function (radio) {
        radio.addEventListener('change', updatePayment);
    });

    updatePayment();

});

</script>

<?php include 'footer.php'; ?>