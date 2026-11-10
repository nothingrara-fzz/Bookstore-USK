<?php
/**
 * FARASTORE - HAPUS BUKU
 *
 * Menghapus buku dari katalog admin.
 * Foto historis pesanan tetap disimpan di order_items.
 */

require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('books.php');
}

$submittedToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['admin_csrf'] ?? '', $submittedToken)) {
    flash('error', 'Permintaan tidak valid. Silakan coba kembali.');
    redirect('books.php');
}

$bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);

if (!$bookId) {
    flash('error', 'ID buku tidak valid.');
    redirect('books.php');
}

$findBook = $pdo->prepare('SELECT id, image FROM books WHERE id = ?');
$findBook->execute([$bookId]);
$book = $findBook->fetch();

if (!$book) {
    flash('error', 'Buku tidak ditemukan atau sudah dihapus.');
    redirect('books.php');
}

try {
    $pdo->beginTransaction();

    // Lepaskan relasi dari riwayat pesanan.
    // Snapshot judul, penulis, harga, dan foto tetap ada di order_items.
    $updateHistory = $pdo->prepare(
        'UPDATE order_items SET book_id = NULL WHERE book_id = ?'
    );
    $updateHistory->execute([$bookId]);

    // Cart akan ikut terhapus karena foreign key ON DELETE CASCADE.
    $deleteBook = $pdo->prepare('DELETE FROM books WHERE id = ?');
    $deleteBook->execute([$bookId]);

    $pdo->commit();

    // Hapus file foto hanya jika foto tersimpan di folder upload buku.
    if (strpos($book['image'], 'assets/uploads/books/') === 0) {
        $imageFile = realpath(__DIR__ . '/../' . $book['image']);
        $uploadFolder = realpath(__DIR__ . '/../assets/uploads/books');

        if (
            $imageFile &&
            $uploadFolder &&
            strpos($imageFile, $uploadFolder . DIRECTORY_SEPARATOR) === 0 &&
            is_file($imageFile)
        ) {
            unlink($imageFile);
        }
    }

    flash('success', 'Buku berhasil dihapus dari katalog.');
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash('error', 'Buku gagal dihapus. Periksa relasi tabel database.');
}

redirect('books.php');
