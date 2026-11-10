
<?php
require_once '../config.php';
require_admin();

// Ambil semua percakapan pelanggan
$s = $pdo->query("
    SELECT
        t.id,
        t.user_id,
        t.subject,
        t.created_at,
        t.updated_at,
        u.name AS user_name,
        u.email AS user_email,

        (SELECT message
         FROM messages
         WHERE thread_id = t.id
         ORDER BY id DESC
         LIMIT 1) AS last_message,

        (SELECT COUNT(*)
         FROM messages
         WHERE thread_id = t.id
           AND sender_role = 'user'
           AND is_read = 0) AS unread_count

    FROM message_threads t
    JOIN users u ON u.id = t.user_id
    ORDER BY t.updated_at DESC
");

$threads = $s->fetchAll();

// Panggil layout dan tampilkan sidebar admin
include_once __DIR__ . '/_layout.php';
admin_header('Pesan Masuk');
?>

<div class="message-admin-page">

    <div class="page-heading">
        <span class="eyebrow">CUSTOMER SERVICE</span>
        <h1>Pesan <em>Masuk.</em></h1>
        <p>Kelola pesan dan pertanyaan dari pelanggan Farastore.</p>
    </div>

    <div class="message-admin-card">

        <div class="message-admin-header">
            <div>
                <h2>Semua Percakapan</h2>
                <p><?= count($threads) ?> percakapan tersimpan</p>
            </div>

            <div class="total-message-icon">
                ✉
            </div>
        </div>

        <?php if (!$threads): ?>

            <div class="empty-message">
                <div class="empty-message-icon">✉</div>
                <h3>Belum Ada Pesan Masuk</h3>
                <p>Pesan dari pelanggan akan muncul di sini.</p>
            </div>

        <?php else: ?>

            <div class="message-table-wrap">

                <table class="message-table">

                    <thead>
                        <tr>
                            <th>Pengirim</th>
                            <th>Judul Pesan</th>
                            <th>Pesan Terakhir</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($threads as $thread): ?>

                            <tr>

                                <td>
                                    <div class="sender-info">

                                        <div class="sender-avatar">
                                            <?= e(strtoupper(substr($thread['user_name'], 0, 1))) ?>
                                        </div>

                                        <div class="sender-details">
                                            <strong>
                                                <?= e($thread['user_name']) ?>
                                            </strong>

                                            <small>
                                                <?= e($thread['user_email']) ?>
                                            </small>
                                        </div>

                                    </div>
                                </td>

                                <td>
                                    <span class="subject-text">
                                        <?= e($thread['subject']) ?>
                                    </span>
                                </td>

                                <td class="last-message">
                                    <?= e($thread['last_message'] ?? '') ?>
                                </td>

                                <td>
                                    <span class="message-date">
                                        <?= date('d/m/Y', strtotime($thread['updated_at'])) ?>
                                    </span>

                                    <small class="message-time">
                                        <?= date('H:i', strtotime($thread['updated_at'])) ?>
                                    </small>
                                </td>

                                <td>

                                    <?php if ((int)$thread['unread_count'] > 0): ?>

                                        <span class="unread-badge">
                                            <?= (int)$thread['unread_count'] ?>
                                            Belum dibaca
                                        </span>

                                    <?php else: ?>

                                        <span class="read-badge">
                                            ✓ Sudah dibaca
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <a
                                        class="view-message-btn"
                                        href="message_detail.php?id=<?= (int)$thread['id'] ?>"
                                    >
                                        Lihat Pesan <span>↗</span>
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

<style>

/* ==================================
   FARASTORE - PESAN MASUK ADMIN
================================== */

.message-admin-page {
    width: 100%;
    padding: 10px 0 35px;
    box-sizing: border-box;
}

/* JUDUL HALAMAN */

.message-admin-page .page-heading {
    margin-bottom: 28px;
}

.message-admin-page .eyebrow {
    display: inline-block;
    color: #e84c78;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 12px;
}

.message-admin-page .page-heading h1 {
    color: #29232d;
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 10px;
    letter-spacing: -1px;
}

.message-admin-page .page-heading h1 em {
    color: #e84c78;
}

.message-admin-page .page-heading p {
    color: #89838d;
    font-size: 14px;
    line-height: 1.7;
    margin: 0;
}

/* CARD UTAMA */

.message-admin-card {
    width: 100%;
    box-sizing: border-box;
    background: #fff;
    border: 1px solid #f2e1e8;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 10px 35px rgba(80, 40, 60, .045);
}

/* HEADER CARD */

.message-admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.message-admin-header h2 {
    color: #2d2630;
    font-size: 21px;
    font-weight: 800;
    margin: 0 0 7px;
}

.message-admin-header p {
    color: #99909a;
    font-size: 12px;
    margin: 0;
}

.total-message-icon {
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: #fff0f5;
    color: #e84c78;
    font-size: 21px;
}

/* TABEL */

.message-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.message-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

.message-table th {
    padding: 15px 12px;
    color: #a49aa3;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-align: left;
    border-bottom: 1px solid #f1e6eb;
    white-space: nowrap;
}

.message-table td {
    padding: 19px 12px;
    border-bottom: 1px solid #f4edf0;
    color: #39323c;
    font-size: 13px;
    vertical-align: middle;
}

.message-table tbody tr {
    transition: background .2s ease;
}

.message-table tbody tr:hover {
    background: #fffafd;
}

.message-table tbody tr:last-child td {
    border-bottom: none;
}

/* INFORMASI PENGIRIM */

.sender-info {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 190px;
}

.sender-avatar {
    width: 39px;
    height: 39px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: linear-gradient(135deg, #fbe0e9, #f8c4d5);
    color: #d83f70;
    font-size: 15px;
    font-weight: 800;
}

.sender-details strong {
    display: block;
    color: #302934;
    font-size: 13px;
    margin-bottom: 5px;
}

.sender-details small {
    display: block;
    color: #99909a;
    font-size: 11px;
}

/* SUBJEK DAN PESAN */

.subject-text {
    color: #302934;
    font-size: 13px;
    font-weight: 700;
}

.last-message {
    max-width: 190px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #88808a !important;
}

/* TANGGAL */

.message-date {
    display: block;
    color: #49414b;
    font-size: 12px;
    white-space: nowrap;
}

.message-time {
    display: block;
    color: #aaa0aa;
    font-size: 11px;
    margin-top: 5px;
}

/* STATUS PESAN */

.unread-badge,
.read-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 11px;
    border-radius: 30px;
    font-size: 10px;
    font-weight: 750;
    white-space: nowrap;
}

.unread-badge {
    background: #fff0f5;
    color: #df4774;
}

.read-badge {
    background: #eaf8ef;
    color: #24945b;
}

/* TOMBOL LIHAT PESAN */

.view-message-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 11px 16px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f05b83, #df3d6d);
    color: white;
    font-size: 11px;
    font-weight: 750;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 5px 12px rgba(225, 61, 109, .13);
    transition: all .25s ease;
}

.view-message-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(225, 61, 109, .23);
}

.view-message-btn span {
    font-size: 14px;
}

/* PESAN KOSONG */

.empty-message {
    padding: 60px 15px;
    text-align: center;
}

.empty-message-icon {
    width: 65px;
    height: 65px;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    background: #fff0f5;
    color: #e84c78;
    font-size: 28px;
}

.empty-message h3 {
    color: #302934;
    font-size: 17px;
    margin: 0 0 9px;
}

.empty-message p {
    color: #99909a;
    font-size: 13px;
    margin: 0;
}

/* RESPONSIVE */

@media(max-width: 768px) {

    .message-admin-page .page-heading h1 {
        font-size: 27px;
    }

    .message-admin-card {
        padding: 20px;
        border-radius: 16px;
    }

    .message-admin-header h2 {
        font-size: 19px;
    }

}

@media(max-width: 480px) {

    .message-admin-page .page-heading h1 {
        font-size: 24px;
    }

    .message-admin-card {
        padding: 15px;
    }

    .message-admin-header {
        align-items: flex-start;
    }

}

</style>

<?php
// Tutup layout admin
admin_footer();
?>
