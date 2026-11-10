<?php
require_once 'config.php';
require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);
$error = '';
$threadId = (int)($_GET['id'] ?? $_POST['thread_id'] ?? 0);

/* =====================================================
   PROSES KIRIM PESAN DAN BALASAN
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // BALAS PERCAKAPAN LAMA
    if ($action === 'reply') {

        $reply = trim($_POST['message'] ?? '');
        $replyThreadId = (int)($_POST['thread_id'] ?? 0);

        if ($replyThreadId <= 0) {
            $error = 'Percakapan tidak ditemukan.';

        } elseif ($reply === '') {
            $error = 'Pesan balasan tidak boleh kosong.';
            $threadId = $replyThreadId;

        } else {

            // Pastikan percakapan milik user
            $check = $pdo->prepare("
                SELECT id
                FROM message_threads
                WHERE id = ? AND user_id = ?
                LIMIT 1
            ");

            $check->execute([$replyThreadId, $userId]);
            $ownedThread = $check->fetch();

            if (!$ownedThread) {

                $error = 'Kamu tidak memiliki akses ke percakapan ini.';
                $threadId = 0;

            } else {

                try {

                    $pdo->beginTransaction();

                    // Simpan balasan pelanggan
                    $insert = $pdo->prepare("
                        INSERT INTO messages
                        (thread_id, sender_role, sender_id, message)
                        VALUES (?, 'user', ?, ?)
                    ");

                    $insert->execute([
                        $replyThreadId,
                        $userId,
                        $reply
                    ]);

                    // Perbarui waktu percakapan
                    $update = $pdo->prepare("
                        UPDATE message_threads
                        SET updated_at = NOW()
                        WHERE id = ? AND user_id = ?
                    ");

                    $update->execute([
                        $replyThreadId,
                        $userId
                    ]);

                    $pdo->commit();

                    redirect('contact.php?id=' . $replyThreadId);
                    exit;

                } catch (Throwable $e) {

                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $error = 'Balasan gagal dikirim. Silakan coba lagi.';
                    $threadId = $replyThreadId;
                }
            }
        }
    }

    // MEMBUAT PESAN BARU
    elseif ($action === 'new') {

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($subject === '' || $message === '') {

            $error = 'Judul dan pesan wajib diisi.';

        } elseif (mb_strlen($subject) > 150) {

            $error = 'Judul pesan maksimal 150 karakter.';

        } else {

            try {

                $pdo->beginTransaction();

                $insertThread = $pdo->prepare("
                    INSERT INTO message_threads (user_id, subject)
                    VALUES (?, ?)
                ");

                $insertThread->execute([
                    $userId,
                    $subject
                ]);

                $newThreadId = (int)$pdo->lastInsertId();

                $insertMessage = $pdo->prepare("
                    INSERT INTO messages
                    (thread_id, sender_role, sender_id, message)
                    VALUES (?, 'user', ?, ?)
                ");

                $insertMessage->execute([
                    $newThreadId,
                    $userId,
                    $message
                ]);

                $pdo->commit();

                redirect('contact.php?id=' . $newThreadId);
                exit;

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Pesan gagal dikirim. Silakan coba lagi.';
            }
        }
    }
}

/* =====================================================
   AMBIL PERCAKAPAN
===================================================== */

$activeThread = null;
$conversation = [];

if ($threadId > 0) {

    $s = $pdo->prepare("
        SELECT *
        FROM message_threads
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");

    $s->execute([$threadId, $userId]);
    $activeThread = $s->fetch();

    if ($activeThread) {

        // Tandai pesan admin sudah dibaca
        $s = $pdo->prepare("
            UPDATE messages
            SET is_read = 1
            WHERE thread_id = ?
            AND sender_role = 'admin'
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
        $conversation = $s->fetchAll();

    } else {

        $threadId = 0;
    }
}

/* =====================================================
   RIWAYAT PESAN PELANGGAN
===================================================== */

$s = $pdo->prepare("
    SELECT
        t.*,
        (
            SELECT message
            FROM messages
            WHERE thread_id = t.id
            ORDER BY id DESC
            LIMIT 1
        ) AS last_message
    FROM message_threads t
    WHERE t.user_id = ?
    ORDER BY t.updated_at DESC
");

$s->execute([$userId]);
$threads = $s->fetchAll();

include 'header.php';
?>

<div class="page-heading">
    <span class="eyebrow">CUSTOMER SERVICE</span>
    <h1>Hubungi Admin.</h1>
</div>

<div class="message-layout">

    <!-- PESAN BARU -->
    <section class="message-card">

        <h2>Pesan Baru</h2>

        <p class="muted">
            Kirim pertanyaan atau pesan kepada admin Farastore.
        </p>

        <?php if ($error && !$activeThread): ?>
            <div class="message-error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="contact.php">

            <input type="hidden" name="action" value="new">

            <div class="field">
                <label for="new-subject">Judul pesan</label>

                <input
                    id="new-subject"
                    type="text"
                    name="subject"
                    maxlength="150"
                    placeholder="Contoh: Pertanyaan tentang pesanan"
                    required
                >
            </div>

            <div class="field">
                <label for="new-message">Isi pesan</label>

                <textarea
                    id="new-message"
                    name="message"
                    rows="5"
                    placeholder="Tuliskan pesan kamu di sini..."
                    required
                ></textarea>
            </div>

            <button class="btn full" type="submit">
                Kirim Pesan ↗
            </button>

        </form>

    </section>

    <!-- RIWAYAT PESAN -->
    <section class="message-card">

        <h2>Riwayat Pesan</h2>

        <?php if (!$threads): ?>

            <div class="message-empty">
                Belum ada pesan. Yuk, kirim pesan pertamamu ♡
            </div>

        <?php else: ?>

            <?php foreach ($threads as $thread): ?>

                <a
                    class="thread-item <?= $threadId === (int)$thread['id'] ? 'active' : '' ?>"
                    href="contact.php?id=<?= (int)$thread['id'] ?>"
                >

                    <strong>
                        <?= e($thread['subject']) ?>
                    </strong>

                    <span>
                        <?= e($thread['last_message'] ?? '') ?>
                    </span>

                    <small>
                        <?= date('d M Y, H:i', strtotime($thread['updated_at'])) ?>
                    </small>

                </a>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</div>

<?php if ($activeThread): ?>

<!-- DETAIL PERCAKAPAN -->
<section class="message-card conversation-card">

    <div class="conversation-heading">

        <div>
            <span class="eyebrow">CONVERSATION</span>

            <h2>
                <?= e($activeThread['subject']) ?>
            </h2>

            <p class="conversation-subtitle">
                Kamu dapat membalas pesan admin kapan saja.
            </p>
        </div>

        <span class="status-label">
            <span class="status-dot"></span>
            Percakapan aktif
        </span>

    </div>

    <!-- RIWAYAT CHAT -->
    <div class="chat-messages" id="chatMessages">

        <?php if (!$conversation): ?>

            <div class="message-empty">
                Belum ada pesan dalam percakapan ini.
            </div>

        <?php else: ?>

            <?php foreach ($conversation as $msg): ?>

                <?php $isMine = $msg['sender_role'] === 'user'; ?>

                <div class="chat-row <?= $isMine ? 'mine' : 'theirs' ?>">

                    <div class="chat-bubble">

                        <small>
                            <?= $isMine ? 'Kamu' : 'Admin Farastore' ?>
                        </small>

                        <p>
                            <?= nl2br(e($msg['message'])) ?>
                        </p>

                        <time>
                            <?= date('d M Y, H:i', strtotime($msg['created_at'])) ?>
                        </time>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <?php if ($error && $activeThread): ?>

        <div class="message-error reply-message-error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <!-- FORM BALASAN PELANGGAN -->
    <form
        method="post"
        action="contact.php?id=<?= (int)$activeThread['id'] ?>"
        class="user-reply-form"
    >

        <input type="hidden" name="action" value="reply">

        <input
            type="hidden"
            name="thread_id"
            value="<?= (int)$activeThread['id'] ?>"
        >

        <label for="reply-message">
            Balas pesan
        </label>

        <div class="reply-input-area">

            <textarea
                id="reply-message"
                name="message"
                rows="3"
                placeholder="Tulis balasan kamu untuk Admin Farastore..."
                required
            ></textarea>

            <button type="submit" class="reply-send-button">
                Kirim <span>↗</span>
            </button>

        </div>

        <small class="reply-hint">
            Pesanmu akan dikirim ke percakapan ini.
        </small>

    </form>

</section>

<?php endif; ?>

<style>

.message-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
}

.message-card {
    background: #fff;
    border: 1px solid #f5dce5;
    border-radius: 18px;
    padding: 24px;
    margin-bottom: 20px;
}

.message-card h2 {
    margin-top: 0;
    font-size: 21px;
}

.message-error {
    padding: 12px 14px;
    background: #fff0f0;
    color: #c62828;
    border: 1px solid #f4d0d0;
    border-radius: 9px;
    margin: 15px 0;
    font-size: 13px;
}

.thread-item {
    display: block;
    text-decoration: none;
    color: #282333;
    padding: 14px;
    border: 1px solid #f3e2e8;
    border-radius: 12px;
    margin-top: 10px;
    transition: .2s ease;
}

.thread-item.active,
.thread-item:hover {
    background: #fff3f7;
    border-color: #ed4f78;
}

.thread-item strong,
.thread-item span,
.thread-item small {
    display: block;
}

.thread-item span {
    color: #777;
    font-size: 13px;
    margin: 7px 0;
    overflow-wrap: anywhere;
}

.thread-item small {
    color: #999;
}

.message-empty {
    padding: 25px 5px;
    color: #888;
    text-align: center;
    font-size: 13px;
}

.conversation-card {
    margin-top: 5px;
    padding: 26px;
}

.conversation-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid #f3e2e8;
    padding-bottom: 20px;
}

.conversation-heading .eyebrow {
    display: inline-block;
    margin-bottom: 8px;
    color: #ed4f78;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
}

.conversation-heading h2 {
    margin: 0 0 6px;
    color: #282333;
}

.conversation-subtitle {
    margin: 0;
    color: #888;
    font-size: 13px;
}

.status-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #16834b;
    background: #eaf8ef;
    border-radius: 20px;
    padding: 9px 13px;
    font-size: 12px;
    white-space: nowrap;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #35b66b;
}

.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 17px;
    min-height: 180px;
    max-height: 480px;
    padding: 24px 4px;
    overflow-y: auto;
}

.chat-row {
    display: flex;
    width: 100%;
}

.chat-row.mine {
    justify-content: flex-end;
}

.chat-row.theirs {
    justify-content: flex-start;
}

.chat-bubble {
    max-width: min(78%, 520px);
    padding: 14px 17px 12px;
    border-radius: 16px 16px 16px 5px;
    background: #f2f3f6;
    overflow-wrap: anywhere;
}

.chat-row.mine .chat-bubble {
    background: #fff0f5;
    border: 1px solid #f8dce6;
    border-radius: 16px 16px 5px 16px;
}

.chat-bubble small {
    display: block;
    color: #e34378;
    font-size: 11px;
    font-weight: 700;
}

.chat-bubble p {
    margin: 8px 0 10px;
    color: #282333;
    font-size: 14px;
    line-height: 1.65;
}

.chat-bubble time {
    display: block;
    color: #9292a0;
    font-size: 10px;
    text-align: right;
}

/* FORM BALASAN */

.user-reply-form {
    padding-top: 20px;
    border-top: 1px solid #f3e2e8;
}

.user-reply-form > label {
    display: block;
    margin-bottom: 10px;
    color: #282333;
    font-size: 14px;
    font-weight: 700;
}

.reply-input-area {
    display: flex;
    align-items: stretch;
    gap: 12px;
}

.reply-input-area textarea {
    flex: 1;
    width: 100%;
    min-width: 0;
    min-height: 85px;
    padding: 14px 15px;
    border: 1px solid #eadce3;
    border-radius: 12px;
    outline: none;
    color: #282333;
    background: #fff;
    font: inherit;
    font-size: 13px;
    line-height: 1.6;
    resize: vertical;
    transition: .2s ease;
}

.reply-input-area textarea:focus {
    border-color: #ed4f78;
    box-shadow: 0 0 0 3px rgba(237, 79, 120, .10);
}

.reply-input-area textarea::placeholder {
    color: #a0a0aa;
}

.reply-send-button {
    align-self: flex-end;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-height: 45px;
    padding: 0 20px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(135deg, #f65b89, #e83d72);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 5px 13px rgba(232, 61, 114, .18);
    transition: .2s ease;
}

.reply-send-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 17px rgba(232, 61, 114, .25);
}

.reply-send-button span {
    font-size: 17px;
}

.reply-hint {
    display: block;
    margin-top: 9px;
    color: #999;
    font-size: 11px;
}

.reply-message-error {
    margin-bottom: 15px;
}

@media(max-width:700px) {

    .message-layout {
        grid-template-columns: 1fr;
    }

    .message-card {
        padding: 17px;
    }

    .conversation-card {
        padding: 18px;
    }

    .conversation-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .chat-bubble {
        max-width: 88%;
    }

    .reply-input-area {
        flex-direction: column;
    }

    .reply-send-button {
        align-self: stretch;
        width: 100%;
    }

}

</style>

<script>
// Otomatis arahkan chat ke pesan paling bawah
document.addEventListener('DOMContentLoaded', function () {
    const chat = document.getElementById('chatMessages');

    if (chat) {
        chat.scrollTop = chat.scrollHeight;
    }
});
</script>

<?php include 'footer.php'; ?>