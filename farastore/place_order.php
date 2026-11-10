<?php
require_once 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

// =====================================
// DATA PENERIMA
// =====================================

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$payment = $_POST['payment'] ?? 'COD';

// Mendukung format direct order baru dan lama.
$directBookId = (int)(
    $_POST['direct_book_id']
    ?? (
        ($_POST['buy_now'] ?? '') == '1'
            ? ($_POST['book_id'] ?? 0)
            : 0
    )
);

$isDirectOrder = $directBookId > 0;

$directQuantity = (int)($_POST['quantity'] ?? 1);

if ($directQuantity < 1) {
    $directQuantity = 1;
}

$checkoutUrl = $isDirectOrder
    ? 'checkout.php?book_id=' . $directBookId . '&quantity=' . $directQuantity
    : 'checkout.php';

// =====================================
// VALIDASI DATA PENERIMA
// =====================================

if ($name === '' || $phone === '' || $address === '') {
    flash('error', 'Lengkapi data penerima.');
    redirect($checkoutUrl);
}

if (!in_array($payment, ['COD', 'Transfer', 'QRIS'], true)) {
    flash('error', 'Metode pembayaran tidak valid.');
    redirect($checkoutUrl);
}

$bank = ($payment === 'Transfer') ? 'SeaBank' : null;
$userId = (int)$_SESSION['user_id'];

// =====================================
// AMBIL BUKU
// =====================================

if ($isDirectOrder) {

    $s = $pdo->prepare("
        SELECT *
        FROM books
        WHERE id = ?
    ");

    $s->execute([$directBookId]);
    $book = $s->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        flash('error', 'Buku tidak ditemukan.');
        redirect('books.php');
    }

    $book['quantity'] = $directQuantity;
    $items = [$book];

} else {

    $s = $pdo->prepare("
        SELECT c.quantity, b.*
        FROM cart_items c
        JOIN books b ON b.id = c.book_id
        WHERE c.user_id = ?
    ");

    $s->execute([$userId]);
    $items = $s->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        flash('error', 'Keranjang masih kosong.');
        redirect('cart.php');
    }
}

// =====================================
// HITUNG TOTAL DAN CEK STOK
// =====================================

$total = 0;

foreach ($items as $it) {

    $quantity = (int)$it['quantity'];

    if ($quantity <= 0 || (int)$it['stock'] < $quantity) {
        flash(
            'error',
            'Stok buku ' . $it['title'] . ' tidak mencukupi.'
        );

        redirect($checkoutUrl);
    }

    $total += (float)$it['price'] * $quantity;
}

// =====================================
// UPLOAD BUKTI PEMBAYARAN
// =====================================

$proof = null;
$uploadedFile = null;

if (in_array($payment, ['Transfer', 'QRIS'], true)) {

    if (
        !isset($_FILES['proof']) ||
        $_FILES['proof']['error'] !== UPLOAD_ERR_OK
    ) {
        flash('error', 'Silakan unggah bukti pembayaran.');
        redirect($checkoutUrl);
    }

    $file = $_FILES['proof'];

    if ($file['size'] > 3 * 1024 * 1024) {
        flash('error', 'Ukuran bukti pembayaran maksimal 3 MB.');
        redirect($checkoutUrl);
    }

    if (!class_exists('finfo')) {
        flash('error', 'Fitur validasi file PHP belum tersedia.');
        redirect($checkoutUrl);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        flash('error', 'Bukti pembayaran harus JPG, PNG, atau WebP.');
        redirect($checkoutUrl);
    }

    $uploadDir = __DIR__ . '/uploads/proofs/';

    if (
        !is_dir($uploadDir) &&
        !mkdir($uploadDir, 0775, true) &&
        !is_dir($uploadDir)
    ) {
        flash('error', 'Folder penyimpanan bukti gagal dibuat.');
        redirect($checkoutUrl);
    }

    $fileName = 'proof_' .
        bin2hex(random_bytes(12)) .
        '.' . $allowed[$mime];

    $destination = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        flash('error', 'Bukti pembayaran gagal disimpan.');
        redirect($checkoutUrl);
    }

    $proof = 'uploads/proofs/' . $fileName;
    $uploadedFile = $destination;
}

// =====================================
// SIMPAN PESANAN
// =====================================

try {

    $pdo->beginTransaction();

    $status = ($payment === 'COD')
        ? 'Pesanan Diproses'
        : 'Menunggu Verifikasi';

    $temporaryCode = 'TMP-' . strtoupper(bin2hex(random_bytes(6)));

    $insertOrder = $pdo->prepare("
        INSERT INTO orders (
            order_code,
            user_id,
            customer_name,
            phone,
            address,
            payment_method,
            bank_name,
            total,
            payment_proof,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insertOrder->execute([
        $temporaryCode,
        $userId,
        $name,
        $phone,
        $address,
        $payment,
        $bank,
        $total,
        $proof,
        $status
    ]);

    // Ambil ID pesanan.
    $orderId = (int)$pdo->lastInsertId();

    // =====================================
    // FORMAT KODE PESANAN
    // =====================================

    $tanggalPesanan = date('dmy');

    $nomorUrut = str_pad(
        (string)$orderId,
        3,
        '0',
        STR_PAD_LEFT
    );

    $code = 'FARA-' . $tanggalPesanan . $nomorUrut;

    $updateOrderCode = $pdo->prepare("
        UPDATE orders
        SET order_code = ?
        WHERE id = ?
    ");

    $updateOrderCode->execute([
        $code,
        $orderId
    ]);

    // =====================================
    // SIMPAN DETAIL PESANAN
    // =====================================

    $insertItem = $pdo->prepare("
        INSERT INTO order_items (
            order_id,
            book_id,
            book_title,
            book_author,
            book_image,
            price,
            quantity
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    // =====================================
    // KURANGI STOK BUKU
    // =====================================

    $updateStock = $pdo->prepare("
        UPDATE books
        SET stock = stock - ?
        WHERE id = ?
        AND stock >= ?
    ");

    foreach ($items as $it) {

        $quantity = (int)$it['quantity'];
        $bookId = (int)$it['id'];

        $updateStock->execute([
            $quantity,
            $bookId,
            $quantity
        ]);

        if ($updateStock->rowCount() !== 1) {
            throw new Exception(
                'Stok buku ' . $it['title'] . ' tidak mencukupi.'
            );
        }

        $insertItem->execute([
            $orderId,
            $bookId,
            $it['title'],
            $it['author'],
            $it['image'],
            $it['price'],
            $quantity
        ]);
    }

    // =====================================
    // KOSONGKAN KERANJANG
    // HANYA JIKA CHECKOUT DARI KERANJANG
    // =====================================

    if (!$isDirectOrder) {

        $deleteCart = $pdo->prepare("
            DELETE FROM cart_items
            WHERE user_id = ?
        ");

        $deleteCart->execute([$userId]);
    }

    // =====================================
    // SELESAIKAN TRANSAKSI
    // =====================================

    $pdo->commit();

    redirect('order_detail.php?id=' . $orderId);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($uploadedFile && is_file($uploadedFile)) {
        unlink($uploadedFile);
    }

    flash(
        'error',
        'Pesanan gagal dibuat. Silakan periksa stok dan coba kembali.'
    );

    redirect($checkoutUrl);
}