
<?php $me = current_user(); ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farastore — Find your next story</title>
    <link rel="stylesheet" href="assets/style.css">

    <style>
        /* NOTIFIKASI POPUP */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: min(380px, calc(100% - 32px));
        }

        .toast {
            position: relative;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 17px 45px 17px 17px;
            background: #ffffff;
            border: 1px solid #f5d5e1;
            border-left: 5px solid #ed6b9a;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(80, 35, 55, 0.14);
            animation: toastIn .4s ease forwards;
            transition: opacity .3s ease, transform .3s ease;
        }

        .toast.success {
            border-left-color: #35b879;
        }

        .toast.error {
            border-left-color: #e05252;
        }

        .toast-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #eaf8f0;
            color: #269b60;
            font-size: 20px;
            font-weight: bold;
        }

        .toast.error .toast-icon {
            background: #fff0f0;
            color: #dc4545;
        }

        .toast-content {
            flex: 1;
            min-width: 0;
        }

        .toast-content strong {
            display: block;
            color: #29232a;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .toast-content p {
            margin: 0;
            color: #777078;
            font-size: 13px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .toast-close {
            position: absolute;
            top: 10px;
            right: 12px;
            border: none;
            background: transparent;
            color: #999;
            font-size: 22px;
            cursor: pointer;
            line-height: 1;
        }

        .toast-close:hover {
            color: #333;
        }

        .toast.hide {
            opacity: 0;
            transform: translateX(30px);
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(35px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* MENU HUBUNGI ADMIN */
        .topbar nav a.active {
            color: #ed4f78;
            font-weight: 700;
        }

        @media (max-width: 480px) {
            .toast-container {
                top: 12px;
                right: 12px;
                width: calc(100% - 24px);
            }
        }
    </style>
</head>

<body>

<header class="topbar">

    <a class="brand" href="index.php">
        <span>
            fara<span>store</span>
            <small>BOOKS & LITTLE JOYS</small>
        </span>
    </a>

    <nav>
        <a href="index.php">Home</a>
        <a href="books.php">Book</a>

        <?php if (is_logged_in()): ?>
            <a href="orders.php">Pesanan</a>
            <a href="contact.php">Hubungi Admin</a>
        <?php endif; ?>

        <a href="about.php">About us</a>
    </nav>

    <div class="nav-actions">

        <?php if (is_logged_in()): ?>

            <a class="cart-link" href="cart.php">
                ♡ <span>Keranjang</span><i><?= cart_count() ?></i>
            </a>

            <a class="avatar" href="profile.php">
                <?= e(strtoupper(substr($me['name'] ?? 'U', 0, 1))) ?>
            </a>

            <a class="login-link" href="logout.php">Keluar</a>

        <?php else: ?>

            <a class="login-link" href="login.php">Masuk</a>
            <a class="btn btn-small" href="register.php">Daftar sekarang</a>

        <?php endif; ?>

    </div>

</header>

<main class="page-shell">

<?php if ($m = flash('success')): ?>

    <div class="toast-container">
        <div class="toast success">
            <div class="toast-icon">✓</div>

            <div class="toast-content">
                <strong>Berhasil!</strong>
                <p><?= e($m) ?></p>
            </div>

            <button type="button" class="toast-close" aria-label="Tutup">
                &times;
            </button>
        </div>
    </div>

<?php endif; ?>

<?php if ($m = flash('error')): ?>

    <div class="toast-container">
        <div class="toast error">
            <div class="toast-icon">!</div>

            <div class="toast-content">
                <strong>Terjadi kesalahan</strong>
                <p><?= e($m) ?></p>
            </div>

            <button type="button" class="toast-close" aria-label="Tutup">
                &times;
            </button>
        </div>
    </div>

<?php endif; ?>

<script>
document.querySelectorAll('.toast').forEach(function(toast) {

    function closeToast() {
        toast.classList.add('hide');

        setTimeout(function() {
            const container = toast.closest('.toast-container');
            if (container) container.remove();
        }, 300);
    }

    const closeButton = toast.querySelector('.toast-close');

    if (closeButton) {
        closeButton.addEventListener('click', closeToast);
    }

    setTimeout(closeToast, 5000);
});
</script>
