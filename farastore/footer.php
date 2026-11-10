<footer class="farastore-footer">

    <div class="footer-main">

        <div class="footer-brand">
            <div class="footer-logo">
                <div>
                    <h2>fara<span>store</span></h2>
                    <small>BOOKS & LITTLE JOYS</small>
                </div>
            </div>

            <p>
                Temukan cerita yang membuat
                hidupmu lebih bermakna.
            </p>
        </div>

        <div class="footer-contact">
            <h3>Kontak Kami</h3>

            <p>
                <i class="fas fa-phone"></i>
                <a href="tel:081234567890">0812-8419-1435</a>
            </p>

            <p>
                <i class="fas fa-envelope"></i>
                <a href="mailto:hello@farastore.id">
                    hello@farastore.id
                </a>
            </p>

            <p>
                <i class="fas fa-map-marker-alt"></i>
                Jl. Pinus2 no.15
            </p>
        </div>

        <div class="footer-links">
            <h3>Tautan Cepat</h3>

            <a href="index.php">Beranda</a>
            <a href="books.php">Buku</a>
            <a href="orders.php">Pesanan</a>
            <a href="about.php">Tentang Kami</a>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© <?= date('Y') ?> Farastore. Fazli&Rara Bookstore</p>

        <div>
            <a href="privacy.php">Kebijakan Privasi</a>
            <span>·</span>
            <a href="terms.php">Syarat & Ketentuan</a>
        </div>
    </div>

</footer>

<style>
.farastore-footer {
    width: calc(100% - 40px);
    margin: 35px auto 15px;
    overflow: hidden;
    border-radius: 25px;
    background: linear-gradient(110deg, #fff5f8, #fde8f1);
    color: #49364a;
}

.footer-main {
    display: grid;
    grid-template-columns: 1.3fr 1fr 1fr;
    gap: 55px;
    padding: 35px 6%;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 18px;
}

.footer-logo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f7b1c9, #e86f9b);
    color: white;
    font-family: 'Playfair Display', serif;
    font-size: 33px;
    font-style: italic;
    box-shadow: 0 7px 18px rgba(222, 93, 140, .2);
}

.footer-logo h2 {
    margin: 0;
    font-size: 22px;
    color: #34283a;
}

.footer-logo h2 span {
    color: #df6890;
}

.footer-logo small {
    display: block;
    margin-top: 3px;
    color: #a18b9b;
    font-size: 9px;
    letter-spacing: 2px;
}

.footer-brand h3 {
    margin: 0 0 7px;
    font-size: 14px;
}

.footer-brand p {
    max-width: 250px;
    margin: 0;
    color: #857487;
    font-size: 13px;
    line-height: 1.7;
}

.footer-contact h3,
.footer-links h3 {
    margin: 8px 0 18px;
    font-size: 16px;
    color: #34283a;
}

.footer-contact p {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 12px 0;
    color: #67566c;
    font-size: 13px;
}

.footer-contact i {
    width: 17px;
    color: #e77ba1;
}

.footer-contact a,
.footer-links a {
    color: #67566c;
    text-decoration: none;
}

.footer-contact a:hover,
.footer-links a:hover {
    color: #d6537e;
}

.footer-links {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

.footer-links h3 {
    margin-bottom: 5px;
}

.footer-links a {
    font-size: 13px;
}

.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 15px 6%;
    border-top: 1px solid #f5dce7;
    background: rgba(255, 222, 236, .35);
}

.footer-bottom p,
.footer-bottom a,
.footer-bottom span {
    margin: 0;
    color: #92788f;
    font-size: 11px;
}

.footer-bottom a {
    text-decoration: none;
}

.footer-bottom a:hover {
    color: #d6537e;
}

@media (max-width: 700px) {
    .farastore-footer {
        width: calc(100% - 20px);
        border-radius: 18px;
    }

    .footer-main {
        grid-template-columns: 1fr;
        gap: 25px;
        padding: 28px;
    }

    .footer-bottom {
        align-items: flex-start;
        flex-direction: column;
        padding: 17px 28px;
    }
}
</style>