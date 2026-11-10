<?php
require_once 'config.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (strlen($pass) < 6) {
        flash('error', 'Password minimal 6 karakter.');
    } else {
        try {
            $s = $pdo->prepare(
                "INSERT INTO users(name,email,phone,address,password) VALUES(?,?,?,?,?)"
            );

            $s->execute([
                $name,
                $email,
                $phone,
                $address,
                password_hash($pass, PASSWORD_DEFAULT)
            ]);

            $_SESSION['user_id'] = $pdo->lastInsertId();

            flash('success', 'Akun berhasil dibuat. Selamat berbelanja!');
            redirect('index.php');

        } catch (PDOException $e) {
            flash('error', 'Email sudah digunakan atau data tidak valid.');
        }
    }
}

include 'header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">

        <span class="eyebrow">JOIN OUR BOOK CLUB</span>

        <h1>Mulai cerita <em>barumu.</em></h1>

        <p>
            Buat akun sekali saja. Data dirimu akan otomatis terisi saat checkout.
        </p>

        <form method="post">

            <div class="field">
                <label>Nama lengkap</label>
                <input
                    name="name"
                    required
                    placeholder="Nama sesuai penerima"
                >
            </div>

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
                <label>Nomor telepon</label>
                <input
                    name="phone"
                    required
                    placeholder="08xxxxxxxxxx"
                >
            </div>

            <div class="field">
                <label>Alamat lengkap</label>
                <textarea
                    name="address"
                    required
                    placeholder="Jalan, nomor rumah, kelurahan, kecamatan, kota"
                ></textarea>
            </div>

            <div class="field">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    minlength="6"
                    placeholder="Minimal 6 karakter"
                >
            </div>

            <button class="btn full">
                Daftar sekarang ↗
            </button>

        </form>

        <div class="auth-foot">
            Sudah punya akun? <a href="login.php">Masuk</a>
        </div>

    </div>
</div>

<?php
include 'footer.php';
?>