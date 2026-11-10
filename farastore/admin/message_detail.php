
<?php
require_once '../config.php';
require_admin();

$threadId = (int)($_GET['id'] ?? $_POST['thread_id'] ?? 0);

if ($threadId <= 0) {
    redirect('messages.php');
}

$s = $pdo->prepare("
    SELECT t.*, u.name AS user_name, u.email AS user_email
    FROM message_threads t
    JOIN users u ON u.id = t.user_id
    WHERE t.id = ?
");
$s->execute([$threadId]);
$thread = $s->fetch();

if (!$thread) {
    redirect('messages.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = trim($_POST['message'] ?? '');

    if ($reply === '') {
        $error = 'Balasan tidak boleh kosong.';
    } else {
        $adminId = (int)($_SESSION['admin_id'] ?? 0);

        $s = $pdo->prepare("
            INSERT INTO messages
            (thread_id, sender_role, sender_id, message)
            VALUES (?, 'admin', ?, ?)
        ");
        $s->execute([$threadId, $adminId, $reply]);

        $s = $pdo->prepare("
            UPDATE message_threads
            SET updated_at = NOW()
            WHERE id = ?
        ");
        $s->execute([$threadId]);

        redirect('message_detail.php?id=' . $threadId);
    }
}

// Tandai pesan pelanggan sudah dibaca
$s = $pdo->prepare("
    UPDATE messages
    SET is_read = 1
    WHERE thread_id = ?
    AND sender_role = 'user'
");
$s->execute([$threadId]);

// Ambil seluruh percakapan
$s = $pdo->prepare("
    SELECT *
    FROM messages
    WHERE thread_id = ?
    ORDER BY created_at ASC, id ASC
");
$s->execute([$threadId]);
$messages = $s->fetchAll();

// Layout admin
include_once __DIR__ . '/_layout.php';
?>

<div class="page-heading">
    <span class="eyebrow">CUSTOMER SERVICE</span>
    <h1>Detail Pesan Masuk</h1>
</div>

<div class="admin-conversation">

    <!-- INFORMASI PELANGGAN -->
    <div class="conversation-info">

        <div class="subject-info">
            <span class="section-label">SUBJEK PESAN</span>
            <h2><?= e($thread['subject']) ?></h2>
            <span class="conversation-status">
                <span class="status-dot"></span>
                Percakapan aktif
            </span>
        </div>

        <div class="customer-info">
            <div class="customer-avatar">
                <?= strtoupper(substr($thread['user_name'], 0, 1)) ?>
            </div>

            <div class="customer-details">
                <strong><?= e($thread['user_name']) ?></strong>
                <span><?= e($thread['user_email']) ?></span>
                <small>Pelanggan Farastore</small>
            </div>
        </div>

    </div>

    <!-- AREA CHAT -->
    <div class="chat-section">

        <div class="chat-heading">
            <div>
                <h3>Riwayat Percakapan</h3>
                <p>Semua pesan antara pelanggan dan admin.</p>
            </div>

            <div class="message-count">
                <?= count($messages) ?> Pesan
            </div>
        </div>

        <div class="admin-chat">

            <?php if (empty($messages)): ?>

                <div class="empty-chat">
                    <span>Belum ada percakapan.</span>
                </div>

            <?php else: ?>

                <?php foreach ($messages as $msg): ?>

                    <div class="admin-chat-row <?= $msg['sender_role'] === 'admin' ? 'admin-sender' : 'user-sender' ?>">

                        <div class="admin-chat-bubble">

                            <div class="bubble-header">
                                <strong>
                                    <?= $msg['sender_role'] === 'admin' ? 'Admin Farastore' : e($thread['user_name']) ?>
                                </strong>
                            </div>

                            <p><?= nl2br(e($msg['message'])) ?></p>

                            <time>
                                <?= date('d M Y, H:i', strtotime($msg['created_at'])) ?>
                            </time>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

    <!-- FORM BALASAN -->
    <?php if ($error): ?>
        <div class="reply-error">
            <strong>Perhatian!</strong>
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="reply-section">

        <div class="reply-heading">
            <div>
                <h3>Balas Pesan Pelanggan</h3>
                <p>Tuliskan pesan yang ingin kamu sampaikan kepada pelanggan.</p>
            </div>
        </div>

        <form method="post" class="reply-form">

            <input
                type="hidden"
                name="thread_id"
                value="<?= $threadId ?>"
            >

            <div class="textarea-wrapper">
                <textarea
                    id="message"
                    name="message"
                    rows="4"
                    placeholder="Tulis balasan untuk pelanggan..."
                    required
                ></textarea>
            </div>

            <div class="reply-footer">

                <a href="messages.php" class="back-message">
                    <span>←</span> Kembali ke Pesan Masuk
                </a>

                <button type="submit" class="reply-button">
                    Kirim Balasan
                    <span>↗</span>
                </button>

            </div>

        </form>

    </div>

</div>

<style>
/* =========================================
   FARASTORE - DETAIL PESAN CUSTOMER SERVICE
   Modern pastel pink / white interface
========================================= */

.admin-conversation,
.admin-conversation * {
    box-sizing: border-box;
}

.admin-conversation {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 22px;
    margin: 0 0 35px;
    color: #20243a;
    font-family: Inter, "Segoe UI", Arial, sans-serif;
}

/* PAGE HEADING */
.page-heading {
    position: relative;
    margin: 0 0 28px;
    padding: 12px 4px 8px;
    font-family: Inter, "Segoe UI", Arial, sans-serif;
}

.page-heading .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 13px;
    color: #f04479;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2.2px;
}

.page-heading .eyebrow::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #f04479;
    box-shadow: 0 0 0 4px #ffe5ee;
}

.page-heading h1 {
    margin: 0 0 9px;
    color: #20243a;
    font-size: clamp(27px, 3vw, 36px);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -1.2px;
}

.page-heading h1 em {
    color: #f04479;
    font-style: italic;
}

.page-heading p {
    margin: 0;
    color: #777d91;
    font-size: 14px;
    line-height: 1.7;
}

/* CUSTOMER / SUBJECT CARD */
.conversation-info {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(280px, .95fr);
    align-items: center;
    gap: 30px;
    min-height: 150px;
    padding: 27px 32px;
    overflow: hidden;
    background:
        radial-gradient(circle at 100% 100%, rgba(255, 213, 229, .55), transparent 24%),
        linear-gradient(115deg, #fff 0%, #fff 65%, #fff7fa 100%);
    border: 1px solid #f5dce6;
    border-radius: 21px;
    box-shadow: 0 8px 26px rgba(75, 35, 55, .055);
}

.conversation-info::after {
    content: "";
    position: absolute;
    right: -48px;
    bottom: -105px;
    width: 220px;
    height: 150px;
    border: 1px solid rgba(244, 91, 139, .12);
    border-radius: 50%;
    box-shadow: 0 -15px 0 10px rgba(244, 91, 139, .035),
                0 -30px 0 20px rgba(244, 91, 139, .025);
    pointer-events: none;
}

.subject-info {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.section-label {
    display: inline-flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 7px 13px;
    border-radius: 30px;
    background: #ffe8f0;
    color: #ed3f78;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.1px;
}

.subject-info h2 {
    margin: 0 0 13px;
    color: #20243a;
    font-size: 25px;
    font-weight: 800;
    line-height: 1.25;
    overflow-wrap: anywhere;
}

.conversation-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 13px;
    border-radius: 30px;
    background: #eaf8ef;
    color: #23894e;
    font-size: 11px;
    font-weight: 700;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #32b66a;
    box-shadow: 0 0 0 3px rgba(50, 182, 106, .12);
}

.customer-info {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 17px;
    min-height: 94px;
    padding-left: 30px;
    border-left: 1px solid #eadde4;
    text-align: left;
}

.customer-avatar {
    display: flex;
    flex: 0 0 82px;
    width: 82px;
    height: 82px;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(145deg, #ffe5ee, #fac4d8);
    color: #e63e76;
    font-size: 34px;
    font-weight: 800;
    box-shadow: inset 0 0 0 1px rgba(232, 64, 119, .06);
}

.customer-details {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 7px;
}

.customer-details strong {
    color: #20243a;
    font-size: 18px;
    font-weight: 800;
}

.customer-details span {
    color: #626a80;
    font-size: 13px;
    overflow-wrap: anywhere;
}

.customer-details small {
    display: inline-flex;
    width: fit-content;
    padding: 6px 10px;
    border-radius: 20px;
    background: #ffeaf1;
    color: #e34378;
    font-size: 10px;
    font-weight: 700;
}

/* CHAT CARD */
.chat-section {
    padding: 23px 25px 25px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #f1e5eb;
    border-radius: 21px;
    box-shadow: 0 8px 26px rgba(75, 35, 55, .05);
}

.chat-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin: 0 0 18px;
    padding-bottom: 17px;
    border-bottom: 1px solid #f1e8ed;
}

.chat-heading > div:first-child {
    position: relative;
    padding-left: 47px;
}

.chat-heading > div:first-child::before {
    content: "✉";
    position: absolute;
    top: 1px;
    left: 0;
    display: flex;
    width: 34px;
    height: 34px;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: linear-gradient(145deg, #ff6b99, #ed3471);
    color: #fff;
    font-size: 17px;
    box-shadow: 0 5px 12px rgba(237, 52, 113, .2);
}

.chat-heading h3,
.reply-heading h3 {
    margin: 0 0 6px;
    color: #20243a;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -.25px;
}

.chat-heading p,
.reply-heading p {
    margin: 0;
    color: #7d8294;
    font-size: 12px;
    line-height: 1.6;
}

.message-count {
    flex-shrink: 0;
    padding: 10px 16px;
    border-radius: 30px;
    background: #ffeaf1;
    color: #e83f77;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

/* CHAT HISTORY */
.admin-chat {
    display: flex;
    min-height: 220px;
    max-height: 470px;
    flex-direction: column;
    gap: 20px;
    padding: 18px 5px 8px;
    overflow-y: auto;
    scroll-behavior: smooth;
}

.admin-chat::-webkit-scrollbar {
    width: 6px;
}

.admin-chat::-webkit-scrollbar-thumb {
    border-radius: 20px;
    background: #f1c5d5;
}

.admin-chat::-webkit-scrollbar-track {
    background: transparent;
}

.empty-chat {
    display: flex;
    min-height: 180px;
    align-items: center;
    justify-content: center;
    color: #9297a7;
    font-size: 13px;
}

.admin-chat-row {
    display: flex;
    width: 100%;
    align-items: flex-end;
    gap: 10px;
}

.user-sender {
    justify-content: flex-start;
}

.admin-sender {
    justify-content: flex-end;
}

.admin-chat-bubble {
    position: relative;
    max-width: min(68%, 520px);
    min-width: 130px;
    padding: 15px 18px 12px;
    overflow-wrap: anywhere;
    background: #f0f1f5;
    border: 1px solid #ececf1;
    border-radius: 19px 19px 19px 5px;
}

.admin-sender .admin-chat-bubble {
    background: linear-gradient(145deg, #fff2f6, #ffe5ee);
    border: 1px solid #f9d9e5;
    border-radius: 19px 19px 5px 19px;
}

.bubble-header {
    margin-bottom: 8px;
}

.bubble-header strong {
    color: #e34378;
    font-size: 12px;
    font-weight: 800;
}

.admin-chat-bubble p {
    margin: 0 0 12px;
    color: #252a3e;
    font-size: 14px;
    line-height: 1.65;
    white-space: normal;
}

.admin-chat-bubble time {
    display: block;
    color: #858b9d;
    font-size: 10px;
    text-align: right;
}

/* REPLY CARD */
.reply-section {
    padding: 24px 26px 22px;
    background: #fff;
    border: 1px solid #f1e5eb;
    border-radius: 21px;
    box-shadow: 0 8px 26px rgba(75, 35, 55, .05);
}

.reply-heading {
    margin-bottom: 16px;
}

.reply-heading > div {
    position: relative;
    padding-left: 42px;
}

.reply-heading > div::before {
    content: "➤";
    position: absolute;
    top: 0;
    left: 0;
    color: #f04479;
    font-size: 25px;
    transform: rotate(-12deg);
}

.reply-form {
    width: 100%;
}

.textarea-wrapper {
    width: 100%;
}

.reply-form textarea {
    display: block;
    width: 100%;
    min-height: 112px;
    box-sizing: border-box;
    padding: 16px 17px;
    border: 1px solid #dfe1e9;
    border-radius: 13px;
    outline: none;
    background: #fff;
    color: #252a3e;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.7;
    resize: vertical;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.reply-form textarea::placeholder {
    color: #9298a8;
}

.reply-form textarea:focus {
    border-color: #f05a88;
    box-shadow: 0 0 0 4px rgba(240, 90, 136, .10);
}

.reply-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 17px;
}

.back-message {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    color: #ed3f78;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: color .2s ease;
}

.back-message:hover {
    color: #bd2458;
}

.back-message span {
    font-size: 21px;
    line-height: 1;
}

.reply-button {
    display: inline-flex;
    min-height: 46px;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 0 23px;
    border: 0;
    border-radius: 30px;
    background: linear-gradient(135deg, #fa4d83, #e92e6c);
    color: #fff;
    font-family: inherit;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 7px 16px rgba(233, 46, 108, .2);
    transition: transform .2s ease, box-shadow .2s ease;
}

.reply-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(233, 46, 108, .27);
}

.reply-button span {
    font-size: 17px;
}

/* ERROR MESSAGE */
.reply-error {
    padding: 13px 16px;
    border: 1px solid #f3c6cc;
    border-radius: 12px;
    background: #fff3f4;
    color: #bd3448;
    font-size: 13px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .conversation-info {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 23px;
    }

    .customer-info {
        padding: 18px 0 0;
        border-top: 1px solid #eadde4;
        border-left: 0;
    }

    .chat-section,
    .reply-section {
        padding: 20px;
    }

    .admin-chat-bubble {
        max-width: 82%;
    }
}

@media (max-width: 520px) {
    .page-heading {
        margin-bottom: 20px;
    }

    .page-heading h1 {
        font-size: 27px;
    }

    .page-heading p {
        font-size: 13px;
    }

    .conversation-info {
        padding: 19px;
        border-radius: 16px;
    }

    .subject-info h2 {
        font-size: 22px;
    }

    .customer-avatar {
        flex-basis: 62px;
        width: 62px;
        height: 62px;
        font-size: 27px;
    }

    .chat-section,
    .reply-section {
        padding: 17px;
        border-radius: 16px;
    }

    .chat-heading {
        align-items: flex-start;
    }

    .chat-heading h3,
    .reply-heading h3 {
        font-size: 16px;
    }

    .chat-heading p,
    .reply-heading p {
        font-size: 11px;
    }

    .message-count {
        padding: 8px 10px;
        font-size: 10px;
    }

    .admin-chat {
        min-height: 180px;
        padding: 12px 2px;
    }

    .admin-chat-bubble {
        max-width: 90%;
        min-width: 0;
        padding: 12px 14px;
    }

    .admin-chat-bubble p {
        font-size: 13px;
    }

    .reply-footer {
        align-items: stretch;
        flex-direction: column-reverse;
    }

    .reply-button {
        width: 100%;
    }

    .back-message {
        padding: 7px 0;
    }
}
</style>

<?php
// Footer admin: cari file yang tersedia tanpa memunculkan warning.
$footerCandidates = [
    __DIR__ . '/_footer.php',
    __DIR__ . '/footer.php',
    dirname(__DIR__) . '/_footer.php',
    dirname(__DIR__) . '/footer.php'
];

foreach ($footerCandidates as $footerFile) {
    if (is_file($footerFile)) {
        include_once $footerFile;
        break;
    }
}
?>
