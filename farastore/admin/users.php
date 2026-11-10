
<?php
/**
 * FARASTORE - DATA PENGGUNA ADMIN
 */

require_once __DIR__ . '/_layout.php';

// ==========================================
// 1. DETEKSI KOLOM DATABASE
// ==========================================

$userColumns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

function findUserColumn($columns, $candidates) {
    foreach ($candidates as $column) {
        if (in_array($column, $columns, true)) {
            return $column;
        }
    }
    return null;
}

$nameColumn = findUserColumn($userColumns, ['name', 'username']);
$emailColumn = findUserColumn($userColumns, ['email']);
$phoneColumn = findUserColumn($userColumns, ['phone', 'phone_number', 'no_telepon', 'telephone']);
$addressColumn = findUserColumn($userColumns, ['address', 'alamat']);
$dateColumn = findUserColumn($userColumns, ['created_at', 'registered_at', 'tanggal_daftar']);

if (!$nameColumn || !$emailColumn) {
    die('Kolom nama atau email tidak ditemukan pada tabel users.');
}

// ==========================================
// 2. PENCARIAN
// ==========================================

$search = trim($_GET['q'] ?? '');

$where = '';
$params = [];

if ($search !== '') {
    $searchColumns = [$nameColumn, $emailColumn];

    if ($phoneColumn) {
        $searchColumns[] = $phoneColumn;
    }

    if ($addressColumn) {
        $searchColumns[] = $addressColumn;
    }

    $conditions = [];

    foreach ($searchColumns as $column) {
        $conditions[] = "u.`$column` LIKE ?";
        $params[] = "%$search%";
    }

    $where = 'WHERE (' . implode(' OR ', $conditions) . ')';
}

// ==========================================
// 3. PAGINATION
// ==========================================

$perPage = 8;
$page = max(1, (int)($_GET['page'] ?? 1));

$countQuery = $pdo->prepare("
    SELECT COUNT(*)
    FROM users u
    $where
");

$countQuery->execute($params);

$totalUsers = (int)$countQuery->fetchColumn();
$totalPages = max(1, (int)ceil($totalUsers / $perPage));

$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// ==========================================
// 4. AMBIL DATA PENGGUNA
// ==========================================

$phoneSelect = $phoneColumn
    ? "u.`$phoneColumn` AS phone"
    : "'' AS phone";

$addressSelect = $addressColumn
    ? "u.`$addressColumn` AS address"
    : "'' AS address";

$dateSelect = $dateColumn
    ? "u.`$dateColumn` AS joined_at"
    : "NULL AS joined_at";

$usersQuery = $pdo->prepare("
    SELECT
        u.id,
        u.`$nameColumn` AS name,
        u.`$emailColumn` AS email,
        $phoneSelect,
        $addressSelect,
        $dateSelect,
        (
            SELECT COUNT(*)
            FROM orders o
            WHERE o.user_id = u.id
        ) AS total_orders
    FROM users u
    $where
    ORDER BY u.id DESC
    LIMIT $perPage OFFSET $offset
");

$usersQuery->execute($params);
$users = $usersQuery->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// 5. HEADER
// ==========================================

admin_header('Data pengguna');
?>

<div class="farastore-users">

    <!-- HEADER HALAMAN -->
    <div class="users-hero">

        <div class="users-hero-content">

            <span class="users-eyebrow">
                FARASTORE ADMIN PANEL
            </span>

            <h1>
                Data <span>Pengguna</span>
            </h1>

            <p>
                Daftar akun yang telah terdaftar di Farastore.
            </p>

        </div>

        <a href="../index.php" class="users-store-button">
            <i class="fas fa-store"></i>
            Lihat Toko
            <i class="fas fa-arrow-up-right-from-square"></i>
        </a>

    </div>


    <!-- PANEL DATA PENGGUNA -->
    <section class="users-panel">

        <!-- PENCARIAN -->
        <div class="users-toolbar">

            <form method="get" class="users-search">

                <button type="submit" aria-label="Cari pengguna">
                    <i class="fas fa-search"></i>
                </button>

                <input
                    type="text"
                    name="q"
                    value="<?= e($search) ?>"
                    placeholder="Cari nama, email, atau nomor telepon..."
                >

                <?php if ($search !== ''): ?>
                    <a href="users.php" class="clear-search" title="Hapus pencarian">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>

            </form>

        </div>


        <!-- TABEL PENGGUNA -->
        <?php if ($users): ?>

            <div class="users-table-wrap">

                <table class="modern-users-table">

                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Pesanan</th>
                            <th>Bergabung</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($users as $user): ?>

                            <tr>

                                <!-- NAMA -->
                                <td>
                                    <div class="user-name-cell">

                                        <div class="user-avatar">
                                            <?= e(strtoupper(substr($user['name'], 0, 1))) ?>
                                        </div>

                                        <strong>
                                            <?= e($user['name']) ?>
                                        </strong>

                                    </div>
                                </td>

                                <!-- EMAIL -->
                                <td>
                                    <div class="user-info-cell">
                                        <i class="far fa-envelope"></i>
                                        <span><?= e($user['email']) ?></span>
                                    </div>
                                </td>

                                <!-- TELEPON -->
                                <td>
                                    <div class="user-info-cell">
                                        <i class="fas fa-phone-alt"></i>
                                        <span><?= e($user['phone'] ?: '-') ?></span>
                                    </div>
                                </td>

                                <!-- ALAMAT -->
                                <td>
                                    <div class="user-info-cell address-cell">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= e($user['address'] ?: '-') ?></span>
                                    </div>
                                </td>

                                <!-- PESANAN -->
                                <td>
                                    <span class="user-order-count">
                                        <i class="fas fa-shopping-bag"></i>
                                        <?= (int)$user['total_orders'] ?>
                                    </span>
                                </td>

                                <!-- TANGGAL BERGABUNG -->
                                <td>
                                    <div class="user-info-cell">
                                        <i class="far fa-calendar-alt"></i>
                                        <span>
                                            <?php
                                            if (!empty($user['joined_at'])) {
                                                echo date(
                                                    'd/m/Y',
                                                    strtotime($user['joined_at'])
                                                );
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- MENU -->
                                <td>
                                    <button
                                        type="button"
                                        class="user-menu-button"
                                        title="Informasi pengguna"
                                        onclick="alert('Pengguna: <?= e(addslashes($user['name'])) ?>')"
                                    >
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <!-- FOOTER TABEL -->
            <div class="users-table-footer">

                <p>
                    <i class="fas fa-info-circle"></i>
                    Menampilkan
                    <?= $totalUsers > 0 ? $offset + 1 : 0 ?>
                    –
                    <?= min($offset + $perPage, $totalUsers) ?>
                    dari <?= $totalUsers ?> pengguna
                </p>

                <!-- PAGINATION -->
                <?php if ($totalPages > 1): ?>

                    <div class="users-pagination">

                        <?php if ($page > 1): ?>
                            <a
                                href="?q=<?= urlencode($search) ?>&page=<?= $page - 1 ?>"
                                class="page-arrow"
                            >
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-arrow disabled">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                            <a
                                href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"
                                class="page-number <?= $page === $i ? 'active' : '' ?>"
                            >
                                <?= $i ?>
                            </a>

                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a
                                href="?q=<?= urlencode($search) ?>&page=<?= $page + 1 ?>"
                                class="page-arrow"
                            >
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-arrow disabled">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

        <?php else: ?>

            <div class="users-empty">

                <div class="users-empty-icon">
                    <i class="fas fa-user-friends"></i>
                </div>

                <h3>
                    <?= $search !== '' ? 'Pengguna tidak ditemukan' : 'Belum ada pengguna' ?>
                </h3>

                <p>
                    <?= $search !== ''
                        ? 'Coba gunakan kata kunci pencarian yang lain.'
                        : 'Pengguna yang mendaftar akan muncul di sini.' ?>
                </p>

            </div>

        <?php endif; ?>

    </section>


    <!-- FOOTER -->
    <div class="users-page-footer">
        <span>FaraStore</span>
        <span class="footer-heart">♡</span>
        <span>Books for a better you</span>
        <div class="footer-line"></div>
    </div>

</div>


<style>

/* =========================================
   FARASTORE - DATA PENGGUNA
========================================= */

.farastore-users {
    position: relative;
    width: 100%;
    min-height: 85vh;
    padding-bottom: 20px;
    color: #39243d;
    box-sizing: border-box;
}

.farastore-users * {
    box-sizing: border-box;
}


/* HEADER */

.users-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    min-height: 190px;
    padding: 30px;
    margin-bottom: 20px;
    border: 1px solid #f5d9e5;
    border-radius: 18px;
    background: linear-gradient(115deg, #fff8fb, #ffeaf3);
}

.users-hero-content {
    position: relative;
}

.users-eyebrow {
    display: inline-block;
    padding: 8px 15px;
    margin-bottom: 13px;
    border-radius: 30px;
    background: #ffd9e8;
    color: #df5083;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
}

.users-hero h1 {
    margin: 0;
    color: #38213d;
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(32px, 4vw, 45px);
    font-weight: 700;
    line-height: 1.2;
}

.users-hero h1 span {
    color: #e9568c;
}

.users-hero p {
    margin: 12px 0 0;
    color: #79657e;
    font-size: 13px;
}

.users-hero .heart {
    color: #ec5c94;
    font-size: 17px;
}


/* TOMBOL TOKO */

.users-store-button {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 14px 20px;
    border-radius: 13px;
    background: linear-gradient(135deg, #ed6399, #df4381);
    color: white;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 7px 18px rgba(223, 67, 129, .2);
    transition: .25s;
}

.users-store-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 22px rgba(223, 67, 129, .3);
}


/* PANEL */

.users-panel {
    position: relative;
    padding: 22px;
    border: 1px solid #f5d9e5;
    border-radius: 20px;
    background: rgba(255, 255, 255, .95);
    box-shadow: 0 12px 35px rgba(108, 58, 82, .055);
}


/* PENCARIAN */

.users-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}

.users-search {
    display: flex;
    align-items: center;
    width: min(100%, 600px);
    height: 49px;
    padding: 0 14px;
    border: 1px solid #f3d4e2;
    border-radius: 13px;
    background: #fffafd;
    transition: .2s;
}

.users-search:focus-within {
    border-color: #e96b9d;
    box-shadow: 0 0 0 3px rgba(233, 107, 157, .08);
}

.users-search button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: #fff0f6;
    border-radius: 9px;
    color: #d84f86;
    cursor: pointer;
}

.users-search input {
    flex: 1;
    min-width: 0;
    padding: 10px 12px;
    border: none;
    outline: none;
    background: transparent;
    color: #49334e;
    font-size: 12px;
}

.users-search input::placeholder {
    color: #a28fa5;
}

.clear-search {
    color: #c86a91;
    text-decoration: none;
    padding: 8px;
}


/* TABEL */

.users-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #f3d8e4;
    border-radius: 15px;
}

.modern-users-table {
    width: 100%;
    min-width: 1050px;
    border-collapse: separate;
    border-spacing: 0;
    background: white;
}

.modern-users-table thead {
    background: linear-gradient(90deg, #fff0f6, #ffe6f0);
}

.modern-users-table th {
    padding: 17px 15px;
    color: #d34d80;
    text-align: left;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .6px;
    text-transform: uppercase;
    white-space: nowrap;
}

.modern-users-table td {
    padding: 13px 15px;
    border-top: 1px solid #f7e7ee;
    color: #49374d;
    font-size: 12px;
    vertical-align: middle;
}

.modern-users-table tbody tr {
    transition: background .2s;
}

.modern-users-table tbody tr:hover {
    background: #fff8fb;
}


/* NAMA PENGGUNA */

.user-name-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 210px;
}

.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #f58bb4, #e94e89);
    color: white;
    font-size: 17px;
    font-weight: 700;
    box-shadow: 0 4px 10px rgba(226, 76, 133, .18);
}

.user-name-cell strong {
    color: #3e2943;
    font-size: 12px;
    font-weight: 700;
}


/* INFORMASI PENGGUNA */

.user-info-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 140px;
}

.user-info-cell i {
    flex-shrink: 0;
    color: #49334e;
    font-size: 14px;
}

.user-info-cell span {
    color: #55435a;
    line-height: 1.5;
}

.address-cell {
    max-width: 210px;
}

.address-cell span {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}


/* JUMLAH PESANAN */

.user-order-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-width: 65px;
    padding: 9px 12px;
    border-radius: 30px;
    background: #fff0f6;
    color: #ce477b;
    font-size: 12px;
    font-weight: 800;
}

.user-order-count i {
    font-size: 13px;
}


/* TOMBOL MENU */

.user-menu-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 50%;
    background: #fff0f6;
    color: #d44c82;
    cursor: pointer;
    transition: .2s;
}

.user-menu-button:hover {
    background: #fbd9e7;
    transform: scale(1.06);
}


/* FOOTER TABEL */

.users-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 17px 8px 0;
}

.users-table-footer p {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0;
    color: #938096;
    font-size: 12px;
}

.users-table-footer p i {
    color: #ec6b9d;
}


/* PAGINATION */

.users-pagination {
    display: flex;
    align-items: center;
    gap: 8px;
}

.page-arrow,
.page-number {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 39px;
    height: 39px;
    padding: 0 10px;
    border: 1px solid #f5d9e5;
    border-radius: 10px;
    background: #fff5f9;
    color: #d75a8a;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    transition: .2s;
}

.page-number.active {
    border-color: #ec6398;
    background: linear-gradient(135deg, #f17eaa, #e84f89);
    color: white;
    box-shadow: 0 4px 10px rgba(226, 76, 133, .18);
}

.page-arrow:hover,
.page-number:not(.active):hover {
    background: #ffe3ee;
}

.page-arrow.disabled {
    opacity: .4;
    cursor: not-allowed;
}


/* DATA KOSONG */

.users-empty {
    padding: 65px 20px;
    text-align: center;
}

.users-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
    margin: 0 auto 17px;
    border-radius: 22px;
    background: #fff0f6;
    color: #e45d93;
    font-size: 27px;
}

.users-empty h3 {
    margin: 0 0 8px;
    color: #49334e;
    font-size: 17px;
}

.users-empty p {
    margin: 0;
    color: #a18fa4;
    font-size: 12px;
}


/* FOOTER HALAMAN */

.users-page-footer {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 30px 10px 10px;
    color: #e66a9c;
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 17px;
    font-style: italic;
}

.footer-heart {
    font-size: 27px;
}

.footer-line {
    width: 90px;
    height: 1px;
    margin-left: 5px;
    background: #ed8bb1;
}


/* RESPONSIVE */

@media (max-width: 650px) {

    .users-hero {
        align-items: flex-start;
        flex-direction: column;
        gap: 20px;
        padding: 25px 18px;
    }

    .users-hero h1 {
        font-size: 32px;
    }

    .users-store-button {
        padding: 11px 14px;
    }

    .users-panel {
        padding: 13px;
        border-radius: 15px;
    }

    .users-search {
        width: 100%;
    }

    .users-table-footer {
        align-items: flex-start;
        flex-direction: column;
    }

    .users-page-footer {
        font-size: 14px;
        flex-wrap: wrap;
    }

}

</style>

<?php admin_footer(); ?>
