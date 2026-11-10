<?php
/**
 * FARASTORE - LOGIN ADMIN
 * Memeriksa username dan password dari tabel admins.
 */

require_once __DIR__ . '/../config.php';

// Jika sudah login, langsung buka dashboard.
if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $query = $pdo->prepare(
        'SELECT id, username, password
         FROM admins
         WHERE username = :username
         LIMIT 1'
    );
    $query->execute(['username' => $username]);
    $admin = $query->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);

        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        // login.php dan index.php berada di folder admin yang sama.
        header('Location: index.php');
        exit;
    }

    $errorMessage = 'Username atau password admin salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin | Farastore</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="admin-modern.css">
</head>
<body>
    <main class="auth-wrap">
        <section class="auth-card">
            <span class="eyebrow">FARASTORE CONTROL ROOM</span>
            <h1>Admin <em>sign in.</em></h1>
            <p>Masuk menggunakan username dan password admin.</p>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert error">
                    <?= e($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="field">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn full">
                    Masuk ke dashboard ↗
                </button>
            </form>

            <div class="auth-foot">
                <a href="../index.php">← Kembali ke Farastore</a>
            </div>
        </section>
    </main>
</body>
</html>
