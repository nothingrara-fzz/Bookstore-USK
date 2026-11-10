<?php
/**
 * FARASTORE - SIMPAN BUKU
 *
 * Memproses tambah buku baru dan edit buku yang sudah ada.
 */

require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('books.php');
}

// Periksa token formulir.
$submittedToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['admin_csrf'] ?? '', $submittedToken)) {
    flash('error', 'Permintaan tidak valid. Silakan coba kembali.');
    redirect('books.php');
}

// Ambil dan validasi data formulir.
$bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT) ?: 0;
$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$category = trim($_POST['category'] ?? '');
$synopsis = trim($_POST['synopsis'] ?? '');
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
$bestseller = isset($_POST['bestseller']) ? 1 : 0;

if (
    $title === '' ||
    $author === '' ||
    $category === '' ||
    $synopsis === '' ||
    $price === false || $price === null || $price < 0 ||
    $stock === false || $stock === null || $stock < 0
) {
    flash('error', 'Periksa kembali data buku. Semua kolom wajib diisi dengan benar.');
    redirect($bookId ? 'books.php?edit=' . $bookId : 'books.php');
}

// Jika sedang edit, pastikan buku memang tersedia.
$currentBook = null;
if ($bookId > 0) {
    $findBook = $pdo->prepare('SELECT * FROM books WHERE id = ?');
    $findBook->execute([$bookId]);
    $currentBook = $findBook->fetch();

    if (!$currentBook) {
        flash('error', 'Buku yang akan diedit tidak ditemukan.');
        redirect('books.php');
    }
}

$imagePath = $currentBook['image'] ?? '';
$uploadedFile = $_FILES['image'] ?? null;

// Proses foto jika admin mengunggah file baru.
if ($uploadedFile && $uploadedFile['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Foto gagal diunggah. Periksa ukuran dan file foto.');
        redirect($bookId ? 'books.php?edit=' . $bookId : 'books.php');
    }

    if ($uploadedFile['size'] > 3 * 1024 * 1024) {
        flash('error', 'Ukuran foto maksimal 3 MB.');
        redirect($bookId ? 'books.php?edit=' . $bookId : 'books.php');
    }

    // Periksa MIME asli file, bukan hanya ekstensi.
    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($uploadedFile['tmp_name']);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedTypes[$mimeType])) {
        flash('error', 'Format foto harus JPG, PNG, atau WEBP.');
        redirect($bookId ? 'books.php?edit=' . $bookId : 'books.php');
    }

    $uploadDirectory = __DIR__ . '/../assets/uploads/books/';

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
        flash('error', 'Folder penyimpanan foto tidak dapat dibuat.');
        redirect($bookId ? 'books.php?edit=' . $bookId : 'books.php');
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
    $destination = $uploadDirectory . $fileName;

    if (!move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
        flash('error', 'Foto gagal disimpan ke folder upload.');
        redirect($bookId ? 'books.php?edit=' . $bookId : 'books.php');
    }

    $imagePath = 'assets/uploads/books/' . $fileName;
}

// Buku baru wajib memiliki foto sampul.
if ($imagePath === '') {
    flash('error', 'Silakan pilih foto sampul buku.');
    redirect('books.php');
}

try {
    if ($bookId > 0) {
        // Memperbarui buku yang sudah ada.
        $update = $pdo->prepare(
            'UPDATE books
             SET title = ?, author = ?, category = ?, price = ?,
                 synopsis = ?, image = ?, bestseller = ?, stock = ?
             WHERE id = ?'
        );

        $update->execute([
            $title,
            $author,
            $category,
            $price,
            $synopsis,
            $imagePath,
            $bestseller,
            $stock,
            $bookId,
        ]);

        // Hapus foto lama hanya jika foto lama berada di folder upload buku.
        if (
            $uploadedFile &&
            $uploadedFile['error'] === UPLOAD_ERR_OK &&
            !empty($currentBook['image']) &&
            strpos($currentBook['image'], 'assets/uploads/books/') === 0
        ) {
            $oldImage = realpath(__DIR__ . '/../' . $currentBook['image']);
            $safeFolder = realpath(__DIR__ . '/../assets/uploads/books');

            if (
                $oldImage &&
                $safeFolder &&
                strpos($oldImage, $safeFolder . DIRECTORY_SEPARATOR) === 0 &&
                is_file($oldImage)
            ) {
                unlink($oldImage);
            }
        }

        flash('success', 'Data buku berhasil diperbarui.');
    } else {
        // Menambahkan buku baru.
        $insert = $pdo->prepare(
            'INSERT INTO books
                (title, author, category, price, synopsis, image, bestseller, stock)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $insert->execute([
            $title,
            $author,
            $category,
            $price,
            $synopsis,
            $imagePath,
            $bestseller,
            $stock,
        ]);

        flash('success', 'Buku baru berhasil ditambahkan ke katalog.');
    }
} catch (PDOException $error) {
    flash('error', 'Data buku gagal disimpan. Periksa struktur tabel books.');
    redirect($bookId ? 'books.php?edit=' . $bookId : 'books.php');
}

redirect('books.php');
