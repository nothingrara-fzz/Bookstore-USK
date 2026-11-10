<?php
require_once 'config.php';
require_login();

$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $pdo->prepare(
        "UPDATE users SET name=?,phone=?,address=? WHERE id=?"
    );

    $s->execute([
        trim($_POST['name']),
        trim($_POST['phone']),
        trim($_POST['address']),
        $u['id']
    ]);

    flash('success', 'Profil berhasil diperbarui.');
    redirect('profile.php');
}

include 'header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">

        <span class="eyebrow">YOUR ACCOUNT</span>

        <h1>Profil <em>saya.</em></h1>

        <p>
            Perbarui informasi yang akan digunakan saat checkout.
        </p>

        <form method="post">

            <div class="field">
                <label>Nama</label>
                <input
                    name="name"
                    value="<?= e($u['name']) ?>"
                    required
                >
            </div>

            <div class="field">
                <label>Email</label>
                <input
                    value="<?= e($u['email']) ?>"
                    disabled
                >
            </div>

            <div class="field">
                <label>Nomor telepon</label>
                <input
                    name="phone"
                    value="<?= e($u['phone']) ?>"
                    required
                >
            </div>

            <div class="field">
                <label>Alamat</label>
                <textarea name="address" required><?= e($u['address']) ?></textarea>
            </div>

            <button class="btn full">
                Simpan perubahan
            </button>

        </form>

    </div>
</div>

<?php
include 'footer.php';
?>