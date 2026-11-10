<?php
/**
 * FARASTORE - KELOLA BUKU
 *
 * Menampilkan daftar buku dan formulir tambah/edit buku.
 * Data disimpan pada tabel books.
 */

require_once __DIR__ . '/_layout.php';

// Token keamanan sederhana untuk formulir admin.
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['admin_csrf'];
$editBook = null;

// Jika URL memiliki ?edit=ID, ambil data buku untuk diedit.
if (isset($_GET['edit'])) {
    $bookId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

    if ($bookId) {
        $statement = $pdo->prepare('SELECT * FROM books WHERE id = ?');
        $statement->execute([$bookId]);
        $editBook = $statement->fetch();

        if (!$editBook) {
            flash('error', 'Buku yang ingin diedit tidak ditemukan.');
            redirect('books.php');
        }
    }
}

// Ambil seluruh buku untuk ditampilkan di tabel.
$books = $pdo->query(
    'SELECT id, title, author, category, price, stock, image
     FROM books
     ORDER BY created_at DESC, id DESC'
)->fetchAll();

$successMessage = flash('success');
$errorMessage = flash('error');

admin_header('Kelola buku');
?>

<style>
    .book-admin-grid {
        display: grid;
        grid-template-columns: minmax(280px, 0.85fr) minmax(0, 1.6fr);
        gap: 22px;
        align-items: start;
    }
    .book-form-panel, .book-list-panel {
        background: #fff;
        border: 1px solid #f1dce4;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 8px 28px rgba(112, 65, 83, .05);
    }
    .book-form-panel h2, .book-list-panel h2 {
        margin: 0 0 7px;
        color: #49343d;
        font-size: 20px;
    }
    .book-form-panel .muted, .book-list-panel .muted {
        color: #8b7880;
        font-size: 13px;
        margin: 0 0 20px;
    }
    .book-field { margin-bottom: 14px; }
    .book-field label {
        display: block;
        margin-bottom: 7px;
        color: #5d4650;
        font-size: 13px;
        font-weight: 600;
    }
    .book-field input, .book-field textarea, .book-field select {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #ead5de;
        border-radius: 10px;
        padding: 11px 12px;
        background: #fff;
        color: #49343d;
        font: inherit;
    }
    .book-field textarea { min-height: 105px; resize: vertical; }
    .book-submit {
        display: inline-flex;
        justify-content: center;
        width: 100%;
        border: 0;
        border-radius: 11px;
        padding: 12px 16px;
        background: #d96b98;
        color: #fff;
        font-weight: 700;
        cursor: pointer;
    }
    .book-cancel {
        display: block;
        text-align: center;
        margin-top: 12px;
        color: #a34e73;
        text-decoration: none;
        font-size: 13px;
    }
    .book-cover-thumb {
        width: 48px;
        height: 64px;
        object-fit: cover;
        border-radius: 7px;
        background: #fff0f5;
        border: 1px solid #f1dce4;
    }
    .book-actions { display: flex; gap: 8px; align-items: center; }
    .book-actions a, .book-actions button {
        border: 0;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: 12px;
        text-decoration: none;
        cursor: pointer;
    }
    .book-edit-link { background: #fff0f5; color: #a34e73; }
    .book-delete-button { background: #fff0f0; color: #bd4545; }
    .book-alert {
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 16px;
        font-size: 13px;
    }
    .book-alert.success { background: #edf9f0; color: #28643b; }
    .book-alert.error { background: #fff0f0; color: #a83243; }
    .book-current-image { margin-top: 8px; }
    @media (max-width: 950px) {
        .book-admin-grid { grid-template-columns: 1fr; }
    }
</style>

<?php if ($successMessage): ?>
    <div class="book-alert success"><?= e($successMessage) ?></div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="book-alert error"><?= e($errorMessage) ?></div>
<?php endif; ?>

<div class="book-admin-grid">
    <section class="book-form-panel">
        <h2><?= $editBook ? 'Edit buku' : 'Tambah buku baru' ?></h2>
        <p class="muted">
            <?= $editBook
                ? 'Perbarui informasi buku yang dipilih.'
                : 'Isi detail buku untuk menambahkannya ke katalog toko.' ?>
        </p>

        <form action="save_book.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input
                type="hidden"
                name="book_id"
                value="<?= (int) ($editBook['id'] ?? 0) ?>"
            >

            <div class="book-field">
                <label for="title">Judul buku</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="180"
                    value="<?= e($editBook['title'] ?? '') ?>"
                    required
                >
            </div>

            <div class="book-field">
                <label for="author">Penulis</label>
                <input
                    type="text"
                    id="author"
                    name="author"
                    maxlength="140"
                    value="<?= e($editBook['author'] ?? '') ?>"
                    required
                >
            </div>

            <div class="book-field">
                <label for="category">Kategori</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    maxlength="100"
                    value="<?= e($editBook['category'] ?? '') ?>"
                    placeholder="Contoh: Novel, Fantasi, Romansa"
                    required
                >
            </div>

            <div class="book-field">
                <label for="price">Harga (Rupiah)</label>
                <input
                    type="number"
                    id="price"
                    name="price"
                    min="0"
                    step="1"
                    value="<?= e($editBook['price'] ?? '') ?>"
                    required
                >
            </div>

            <div class="book-field">
                <label for="stock">Stok buku</label>
                <input
                    type="number"
                    id="stock"
                    name="stock"
                    min="0"
                    value="<?= e($editBook['stock'] ?? '0') ?>"
                    required
                >
            </div>

            <div class="book-field">
                <label for="synopsis">Sinopsis</label>
                <textarea
                    id="synopsis"
                    name="synopsis"
                    required
                ><?= e($editBook['synopsis'] ?? '') ?></textarea>
            </div>

            <div class="book-field">
                <label for="image">Foto sampul buku</label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                    <?= $editBook ? '' : 'required' ?>
                >
                <small class="muted">Format JPG, PNG, atau WEBP. Maksimal 3 MB.</small>

                <?php if ($editBook && !empty($editBook['image'])): ?>
                    <div class="book-current-image">
                        <img
                            src="../<?= e($editBook['image']) ?>"
                            alt="Sampul buku saat ini"
                            class="book-cover-thumb"
                        >
                        <small class="muted">Kosongkan foto jika tidak ingin menggantinya.</small>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="book-submit">
                <?= $editBook ? 'Simpan perubahan buku' : 'Tambah buku' ?>
            </button>

            <?php if ($editBook): ?>
                <a class="book-cancel" href="books.php">Batal edit</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="book-list-panel">
        <h2>Daftar buku</h2>
        <p class="muted">
            <?= count($books) ?> buku terdaftar di katalog Farastore.
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Sampul</th>
                        <th>Judul dan penulis</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$books): ?>
                        <tr>
                            <td colspan="5">Belum ada buku. Tambahkan buku pertama.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td>
                                    <img
                                        class="book-cover-thumb"
                                        src="../<?= e($book['image']) ?>"
                                        alt="<?= e($book['title']) ?>"
                                    >
                                </td>
                                <td>
                                    <strong><?= e($book['title']) ?></strong><br>
                                    <small><?= e($book['author']) ?></small>
                                </td>
                                <td><?= rupiah($book['price']) ?></td>
                                <td><?= (int) $book['stock'] ?></td>
                                <td>
                                    <div class="book-actions">
                                        <a
                                            class="book-edit-link"
                                            href="books.php?edit=<?= (int) $book['id'] ?>"
                                        >Edit</a>

                                        <form
                                            action="delete_book.php"
                                            method="post"
                                            onsubmit="return confirm('Yakin ingin menghapus buku ini? Buku tidak akan tampil lagi di katalog.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($csrfToken) ?>"
                                            >
                                            <input
                                                type="hidden"
                                                name="book_id"
                                                value="<?= (int) $book['id'] ?>"
                                            >
                                            <button
                                                type="submit"
                                                class="book-delete-button"
                                            >Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php admin_footer(); ?>