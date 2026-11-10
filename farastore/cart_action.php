<?php
require_once 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['book_id'] ?? 0);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    $s = $pdo->prepare(
        "SELECT id,stock FROM books WHERE id=?"
    );
    $s->execute([$id]);
    $b = $s->fetch();

    if ($b) {
        $s = $pdo->prepare(
            "INSERT INTO cart_items(user_id,book_id,quantity)
            VALUES(?,?,?)
            ON DUPLICATE KEY UPDATE
            quantity=LEAST(quantity+VALUES(quantity),?)"
        );

        $s->execute([
            $_SESSION['user_id'],
            $id,
            $qty,
            max(1, (int)$b['stock'])
        ]);

        flash('success', 'Buku berhasil ditambahkan ke keranjang.');
    } else {
        flash('error', 'Buku tidak ditemukan.');
    }
}

$back = $_SERVER['HTTP_REFERER'] ?? 'books.php';

if (strpos($back, 'cart.php') !== false) {
    $back = 'books.php';
}

redirect($back);
?>