
<?php
require_once 'config.php';
include 'header.php';
?>

<style>
/* =====================================
   FARASTORE - ABOUT US
   CLEAN, SIMPLE & ELEGANT
===================================== */

.about-page {
    max-width: 1200px;
    margin: auto;
    padding: 65px 35px 75px;
    color: #392d3b;
}

/* HERO */

.about-heading {
    text-align: center;
    margin-bottom: 50px;
}

.about-decoration {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 15px;
    color: #df6991;
    font-size: 23px;
}

.about-decoration span {
    width: 90px;
    height: 1px;
    background: #efb4c9;
}

.about-heading h1 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(48px, 6vw, 76px);
    font-weight: 600;
    line-height: 1.15;
}

.about-heading h1 em {
    color: #df6991;
}

.about-subtitle {
    margin-top: 12px;
    color: #82727f;
    font-size: 17px;
    letter-spacing: 1px;
}

/* INTRODUCTION */

.about-introduction {
    width: 100%;
    max-width: 780px;
    margin: 0 auto 60px;
    padding: 0 15px;
    text-align: left;
}

.about-introduction p {
    margin: 0 0 22px;
    color: #665966;
    font-size: 15px;
    line-height: 1.9;
    letter-spacing: 0.1px;
    text-align: justify;
    text-justify: inter-word;
}

.about-introduction p:last-child {
    margin-bottom: 0;
}

/* SECTION TITLE */

.about-section-title {
    margin-bottom: 30px;
    text-align: center;
}

.about-section-title h2 {
    margin: 0 0 10px;
    font-family: 'Playfair Display', serif;
    font-size: 34px;
    color: #403342;
}

.about-section-title p {
    margin: 0;
    color: #93838f;
    font-size: 14px;
}

/* VISION & MISSION */

.about-vision-mission {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    margin-bottom: 65px;
}

.about-card {
    padding: 35px 30px;
    border-radius: 25px;
    background: linear-gradient(
        135deg,
        #fff0f5,
        #fbe7ef
    );
    text-align: center;
    transition: .3s ease;
}

.about-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(190, 100, 135, .10);
}

.about-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 65px;
    height: 65px;
    margin: 0 auto 17px;
    border-radius: 50%;
    background: #df6991;
    color: white;
    font-size: 29px;
}

.about-card-label {
    display: inline-block;
    margin-bottom: 15px;
    padding: 9px 25px;
    border-radius: 30px;
    background: #fbdde8;
    color: #d75e89;
    font-size: 17px;
    font-weight: 700;
}

.about-card h3 {
    max-width: 390px;
    margin: 0 auto 15px;
    color: #493847;
    font-family: 'Playfair Display', serif;
    font-size: 21px;
    line-height: 1.5;
}

.about-card-line {
    width: 75px;
    height: 2px;
    margin: 15px auto;
    background: #df6991;
}

.about-card p {
    margin: 0 auto;
    max-width: 410px;
    color: #766774;
    font-size: 14px;
    line-height: 1.9;
}

/* VALUES */

.about-values {
    margin-bottom: 55px;
}

.about-values-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.about-value-item {
    padding: 15px 12px;
    text-align: center;
    border-right: 1px solid #f4d6e1;
}

.about-value-item:last-child {
    border-right: none;
}

.about-value-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 75px;
    height: 75px;
    margin: 0 auto 13px;
    border-radius: 50%;
    background: #fce9f0;
    color: #df6991;
    font-size: 31px;
}

.about-value-item h3 {
    margin: 0 0 8px;
    color: #493847;
    font-size: 16px;
}

.about-value-item p {
    margin: 0;
    color: #817481;
    font-size: 13px;
    line-height: 1.8;
}

/* CLOSING BANNER */

.about-closing {
    padding: 28px 25px;
    border-radius: 25px;
    background: linear-gradient(
        110deg,
        #fbe6ee,
        #fff0f5
    );
    text-align: center;
}

.about-closing p {
    max-width: 750px;
    margin: 0 auto;
    color: #493847;
    font-family: 'Playfair Display', serif;
    font-size: 23px;
    font-style: italic;
    font-weight: 600;
    line-height: 1.6;
}

.about-closing span {
    display: block;
    margin-top: 8px;
    color: #df6991;
    font-size: 25px;
}

/* BOTTOM DECORATION */

.about-bottom-decoration {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-top: 35px;
    color: #df6991;
    font-size: 22px;
}

.about-bottom-decoration span {
    width: 150px;
    height: 1px;
    background: #efb4c9;
}

/* RESPONSIVE */

@media (max-width: 800px) {
    .about-page {
        padding: 45px 22px 55px;
    }

    .about-vision-mission {
        grid-template-columns: 1fr;
    }

    .about-values-grid {
        grid-template-columns: repeat(2, 1fr);
        row-gap: 25px;
    }

    .about-value-item:nth-child(2) {
        border-right: none;
    }
}

@media (max-width: 480px) {
    .about-heading h1 {
        font-size: 46px;
    }

    .about-subtitle {
        font-size: 14px;
    }

    .about-introduction p {
        font-size: 14px;
        line-height: 1.85;
    }

    .about-section-title h2 {
        font-size: 28px;
    }

    .about-card {
        padding: 28px 20px;
    }

    .about-values-grid {
        gap: 12px;
    }

    .about-value-item {
        padding: 12px 7px;
    }

    .about-value-item h3 {
        font-size: 14px;
    }

    .about-value-item p {
        font-size: 12px;
    }

    .about-closing p {
        font-size: 19px;
    }

    .about-decoration span {
        width: 55px;
    }
}
</style>

<main class="about-page">

    <!-- ABOUT HEADING -->

    <section class="about-heading">

        <div class="about-decoration">
            <span></span>
            ✦
            <span></span>
        </div>

        <h1>
            About <em>Us</em>
        </h1>

    </section>


    <!-- INTRODUCTION -->

    <section class="about-introduction">

        <p>
            Farastore adalah toko buku online yang hadir untuk
            menemani perjalanan literasimu. Kami percaya bahwa
            buku bukan hanya sekadar bacaan, tetapi juga jendela
            untuk mengenal dunia, memperluas wawasan, dan
            menemukan versi terbaik dari diri sendiri.
        </p>

        <p>
            Dengan koleksi buku yang beragam, mulai dari novel,
            self-improvement, hingga buku pelajaran, Farastore
            berkomitmen untuk memberikan pengalaman belanja
            yang mudah, aman, dan menyenangkan.
        </p>

        <p>
            Kami ingin setiap pelanggan dapat menemukan buku
            favoritnya dengan nyaman. Bagi kami, setiap buku
            memiliki cerita dan setiap pembaca memiliki
            perjalanan yang istimewa.
        </p>

    </section>


    <!-- VISION & MISSION -->

    <section class="about-vision-mission">

        <!-- VISI -->

        <div class="about-card">

            <span class="about-card-label">
                Visi Kami
            </span>

            <h3>
                Menjadi toko buku online terpercaya di Indonesia
            </h3>

            <div class="about-card-line"></div>

            <p>
                Kami bercita-cita untuk menjadi platform buku
                online yang selalu diandalkan oleh pembaca
                di seluruh Indonesia, dengan menyediakan
                koleksi buku berkualitas, pilihan yang beragam,
                serta pelayanan terbaik bagi setiap pelanggan.
            </p>

        </div>


        <!-- MISI -->

        <div class="about-card">

            <span class="about-card-label">
                Misi Kami
            </span>

            <h3>
                Menyebarkan semangat membaca dan mencerdaskan kehidupan
            </h3>

            <div class="about-card-line"></div>

            <p>
                Kami berkomitmen untuk menyediakan buku
                berkualitas, memberikan pelayanan yang ramah,
                serta menghadirkan pengalaman belanja yang
                nyaman bagi setiap pelanggan. Kami juga ingin
                ikut menumbuhkan minat baca di masyarakat.
            </p>

        </div>

    </section>


    <!-- NILAI-NILAI KAMI -->

    <section class="about-values">

        <div class="about-section-title">

            <div class="about-decoration">
                <span></span>
                ✦
                <span></span>
            </div>

            <h2>
                Nilai-Nilai Kami
            </h2>

            <p>
                Yang menjadi prinsip dalam setiap langkah kami
            </p>

        </div>


        <div class="about-values-grid">

            <div class="about-value-item">
                <h3>Kualitas</h3>

                <p>
                    Kami selalu menghadirkan buku
                    berkualitas terbaik untuk pembaca.
                </p>

            </div>


            <div class="about-value-item">

                <h3>Pelayanan</h3>

                <p>
                    Kepuasan pelanggan adalah
                    prioritas utama kami.
                </p>

            </div>


            <div class="about-value-item">

                <h3>Inovasi</h3>

                <p>
                    Kami terus berkembang untuk
                    memberikan pengalaman lebih baik.
                </p>

            </div>


            <div class="about-value-item">

                <h3>Kepercayaan</h3>

                <p>
                    Kami menjaga kepercayaan
                    setiap pelanggan dengan sepenuh hati.
                </p>

            </div>

        </div>

    </section>

    <!-- BOTTOM DECORATION -->

    <div class="about-bottom-decoration">
        <span></span>
        ✦
        <span></span>
    </div>

</main>

<?php include 'footer.php'; ?>
