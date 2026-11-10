<?php
require_once 'config.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    $s = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $s->execute([$email]);
    $u = $s->fetch();

    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        $_SESSION['user_id'] = $u['id'];

        flash('success', 'Selamat datang kembali, '.$u['name'].'!');
        redirect('index.php');
    }

    flash('error', 'Email atau password tidak sesuai.');
}

include 'header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">

        <h1>Login</h1>

        <form method="post">

            <div class="field">
                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    required
                    placeholder="nama@email.com"
                >
            </div>

            <div class="field">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    placeholder="Masukkan password"
                >
            </div>

            <button class="btn full">
                Masuk ke Farastore ↗
            </button>

        </form>

        <div class="auth-foot">
            Belum punya akun? <a href="register.php">Buat akun</a>
        </div>

    </div>
</div>

<?php
include 'footer.php';
?>