
<?php
require_once 'config.php';
require_login();

$id = (int)($_POST['id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if ($id <= 0) {
    redirect('cart.php');
}

// HAPUS BUKU
if (isset($_POST['delete'])) {

    $s = $pdo->prepare("
        DELETE FROM cart_items
        WHERE user_id = ? AND book_id = ?
    ");

    $s->execute([$userId, $id]);

    redirect('cart.php');
}

// UPDATE JUMLAH BUKU
if (isset($_POST['update'])) {

    $quantity = (int)($_POST['quantity'] ?? 1);
    $quantity = max(1, $quantity);

    // Periksa stok buku
    $s = $pdo->prepare("
        SELECT stock
        FROM books
        WHERE id = ?
    ");

    $s->execute([$id]);
    $book = $s->fetch();

    if (!$book || (int)$book['stock'] <= 0) {
        redirect('cart.php');
    }

    // Jumlah tidak boleh melebihi stok
    $stock = (int)$book['stock'];
    $quantity = min($quantity, $stock);

    $s = $pdo->prepare("
        UPDATE cart_items
        SET quantity = ?
        WHERE user_id = ? AND book_id = ?
    ");

    $s->execute([$quantity, $userId, $id]);
}

redirect('cart.php');
