-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 08:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farastore`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$12$rFYblOgAktPpn/6hY4N7XuOXFnjOnLkuM3aMWO6CsmNoI8dFMn5Le');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(180) NOT NULL,
  `author` varchar(140) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `synopsis` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `bestseller` tinyint(1) DEFAULT 0,
  `stock` int(11) DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `category`, `price`, `synopsis`, `image`, `bestseller`, `stock`, `created_at`) VALUES
(1, 'Laut Bercerita', 'Leila S. Chudori', 'Novel', 98000.00, 'Kisah tentang kehilangan, persahabatan, keluarga, dan perjuangan yang membekas dalam ingatan.', 'assets/uploads/books/10b5e6cf8db4b9e788c2632d7fb0f4c8.jpg', 1, 24, '2026-11-03 02:26:30'),
(2, 'Pulang', 'Tere Liye', 'Fiksi', 89000.00, 'Perjalanan tentang keluarga, keberanian, dan arti pulang yang sesungguhnya.', 'assets/uploads/books/425bb8966e11c6df06c953cb96af6867.jpg', 1, 28, '2026-11-03 02:26:30'),
(4, 'Bumi', 'Tere Liye', 'Fantasi', 95000.00, 'Petualangan seru menuju dunia paralel dengan rahasia dan kekuatan yang tak terduga.', 'assets/uploads/books/5c2e05ed477712d9a89622faac2181b4.jpg', 1, 19, '2026-11-03 02:26:30'),
(6, 'Cantik Itu Luka', 'Eka Kurniawan', 'Sastra', 125000.00, 'Kisah keluarga yang berkelindan dengan sejarah, cinta, tragedi, dan realitas sosial.', 'assets/uploads/books/622dac9fd32744a7889e93ce843cb710.jpg', 0, 12, '2026-11-03 02:26:30'),
(7, 'The Midnight Library', 'Matt Haig', 'Novel', 108000.00, 'Di antara hidup dan mati, sebuah perpustakaan memberi kesempatan melihat pilihan hidup lain.', 'assets/uploads/books/f2af03268a76872e65b98163a495b6b8.jpg', 0, 19, '2026-11-03 02:26:30'),
(8, 'Sebuah Seni untuk Bersikap Bodo Amat', 'Mark Manson', 'Pengembangan Diri', 99000.00, 'Cara pandang realistis untuk memilih hal yang benar-benar penting dalam hidup.', 'assets/uploads/books/deec99c86960ddbab4b52dcb5f43c381.jpg', 0, 16, '2026-11-03 02:26:30'),
(9, 'Hujan', 'Tere Liye', 'Novel', 87000.00, 'Kisah tentang persahabatan, kehilangan, dan masa depan di tengah dunia yang berubah.', 'assets/uploads/books/cf10bd724ee5659cfd781e45e5132240.jpg', 0, 28, '2026-11-03 02:26:30'),
(10, 'Perahu Kertas', 'Dee Lestari', 'Romansa', 102000.00, 'Dua jiwa muda mengejar mimpi, menemukan diri, dan memahami arti cinta.', 'assets/uploads/books/45d328b2c026a30921012e134be2700e.jpg', 0, 14, '2026-11-03 02:26:30'),
(11, '0 Mdpl', 'Nurwina Sari', 'Novel', 120000.00, '0 MDPL melanjutkan kisah Rangga Raja setelah turun dari puncak Gunung Rinjani (3726 MDPL). Jika pendakian gunung sebelumnya dipenuhi perjuangan fisik dan emosional bersama Andini Hangura, novel ini menceritakan kehidupan Rangga saat kembali ke dataran rendah—titik nol meter di atas permukaan laut—di mana ia harus menghadapi kenyataan hidup dan menata kembali hatinya yang sempat terluka.', 'assets/uploads/books/cc5917d2609e6de380addff1975fa059.jpg', 0, 22, '2026-11-04 01:10:01'),
(13, 'Rindu yang Tak pernah Sirna', 'Nur Diyafiqa', 'Fiksi', 115000.00, 'Buku ini masuk ke dalam kategori kumpulan puisi (antologi). Isinya merupakan curahan suara batin penyair mengenai fenomena kehidupan, kerinduan, dan kepasrahan kepada Tuhan. Buku ini bahkan sempat masuk ke dalam nominasi Buku Sastra versi Lumbung Puisi.', 'assets/uploads/books/e5d91ed067ddbb9ee40fa8810d0c3f17.jpg', 0, 6, '2026-11-04 02:18:56'),
(14, 'Seribu Wajah Ayah', 'Nurun Ala', 'Fiksi', 150000.00, 'Seribu Wajah Ayah karya Nurun Ala mengisahkan perjalanan emosional seorang anak tanpa nama (disebut sebagai \"Kamu\") yang pulang ke kampung halamannya dengan penyesalan mendalam setelah sang ayah meninggal dunia.', 'assets/uploads/books/c74f534bffd388c1dcddd760b9eb11fc.jpg', 0, 14, '2026-11-04 02:23:17'),
(15, 'Sabtu Bersama Bapak', 'Adhitya Mulya', 'Fiksi', 109000.00, 'Sabtu Bersama Bapak karya Adhitya Mulya mengisahkan tentang kasih sayang seorang ayah yang melampaui kematian melalui rekaman video yang ia tinggalkan untuk kedua putranya.', 'assets/uploads/books/8c2f0caf5ed0092d52f53d73a31c5862.jpg', 0, 35, '2026-11-04 02:29:15'),
(16, 'Tulisan Sastra', 'Tenderlova', 'Fiksi', 76000.00, 'Novel yang Anda maksud sebenarnya berjudul Tulisan Sastra karya Tenderlova (tokoh utamanya bernama Andhika Sastra Gautama). Novel populer ini berawal dari cerita alternatif fiksi (alternative fiction atau AU) di Wattpad/X yang sangat viral sebelum akhirnya dibukukan', 'assets/uploads/books/b654fa1a30d4724d6acd1b1c31a111e2.jpg', 0, 23, '2026-11-04 02:31:35'),
(17, 'Yang Telah Lama Pergi', 'Tere Liye', 'Fiksi', 67000.00, 'Yang Telah Lama Pergi karya Tere Liye adalah sebuah novel fiksi sejarah (historical fiction) bertema petualangan, pengkhianatan, dan pembalasan dendam yang berlatar pada abad ke-13 di era keemasan Kerajaan Sriwijaya. Buku yang ditulis berdasarkan riset selama 6 tahun ini menyajikan kisah yang berfokus pada konflik maritim, perompak, serta rahasia masa lalu.', 'assets/uploads/books/10f9b3eb3a50258ad8e2e00fb9b8aa8f.jpg', 1, 7, '2026-11-04 02:33:31'),
(18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'Fiksi', 77000.00, 'Buku ini tidak berisi satu cerita panjang tunggal, melainkan kumpulan dari 8 cerita horor pendakian berbeda. Seluruh ceritanya ditulis berdasarkan kompilasi kisah nyata hasil wawancara langsung dengan para narasumber/pendaki yang mengalami kejadian tersebut.', 'assets/uploads/books/5ebc0ad49839cb267e6536d1d0d74372.jpg', 1, 7, '2026-11-04 02:35:59'),
(19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'Fiksi', 99000.00, 'Buku ini merupakan sebuah kumpulan perjalanan hidup, refleksi, dan curahan hati penulis ketika ia dipaksa melewati proses pendewasaan sebelum waktunya akibat benturan realitas keadaan yang pelik.', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 0, 29, '2026-11-04 02:38:00'),
(20, 'Tentang Kamu', 'Tere Liye', 'Drama', 80000.00, 'Novel ini membentang melintasi waktu dengan berlatar belakang sejarah Indonesia (termasuk era Orde Lama) hingga masa modern di Eropa. Ceritanya fokus pada biografi perjalanan hidup satu karakter dari kecil hingga akhir hayatnya.', 'assets/uploads/books/2d7bcc632a72706643270028cbebec43.jpg', 0, 4197, '2026-11-04 02:41:08'),
(22, 'Kala Itu Langit Biru', 'Kim Chun Hei', 'Novel', 67000.00, 'Novel \"Kala itu langit biru\" (atau fiksi penggemar/alternatif yang Anda maksud) menceritakan kisah tentang perjalanan emosional, memori masa lalu, dan pencarian harapan baru di bawah metafora langit yang berubah warna.', 'assets/uploads/books/4c7f4e8dafc92007a3901cc1db0e6b53.jpg', 0, 77, '2026-11-06 13:01:17');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `book_id`, `quantity`) VALUES
(35, 2, 4, 1),
(55, 8, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `sender_role` enum('user','admin') NOT NULL,
  `sender_id` int(11) NOT NULL DEFAULT 0,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `thread_id`, `sender_role`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'user', 6, 'contoh', 1, '2026-11-10 08:31:26'),
(2, 1, 'admin', 1, 'kenapa sih', 1, '2026-11-10 08:32:08'),
(3, 1, 'admin', 1, 'hah', 0, '2026-11-10 08:56:56'),
(4, 2, 'user', 4, 'haii', 1, '2026-11-10 08:58:09'),
(5, 2, 'admin', 1, 'hai', 1, '2026-11-10 08:58:25'),
(6, 3, 'user', 4, 'ya', 1, '2026-11-10 08:58:46'),
(7, 2, 'user', 4, 'ini ga dikirim\" sih', 1, '2026-11-10 09:05:24'),
(8, 2, 'admin', 1, 'YA SABAR', 1, '2026-11-10 09:05:47'),
(9, 2, 'user', 4, 'OHH IYE', 1, '2026-11-10 09:05:59'),
(10, 2, 'admin', 1, 'YA', 1, '2026-11-10 09:06:38'),
(11, 4, 'user', 7, 'halo', 1, '2026-11-10 11:05:10'),
(12, 4, 'admin', 1, 'iya halo', 1, '2026-11-10 11:05:24'),
(13, 4, 'user', 7, 'ini jelek', 1, '2026-11-10 11:05:35'),
(14, 4, 'admin', 1, 'yauda sih', 1, '2026-11-10 11:05:46');

-- --------------------------------------------------------

--
-- Table structure for table `message_threads`
--

CREATE TABLE `message_threads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_threads`
--

INSERT INTO `message_threads` (`id`, `user_id`, `subject`, `created_at`, `updated_at`) VALUES
(1, 6, 'Apa aja', '2026-11-10 08:31:26', '2026-11-10 08:56:56'),
(2, 4, 'Tentang pesanan', '2026-11-10 08:58:09', '2026-11-10 09:06:38'),
(3, 4, 'nihao', '2026-11-10 08:58:46', '2026-11-10 08:58:46'),
(4, 7, 'hai', '2026-11-10 11:05:10', '2026-11-10 11:05:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(40) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `bank_name` varchar(80) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'Menunggu Pembayaran',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `customer_name`, `phone`, `address`, `payment_method`, `bank_name`, `total`, `payment_proof`, `status`, `created_at`) VALUES
(1, 'FARA-261104-B9B705', 2, 'Aurora Eka Marpaung', '088212421433', 'jl.sekarang', 'COD', NULL, 187000.00, NULL, 'Selesai', '2026-11-04 00:10:43'),
(2, 'FARA-261104-3D27DB', 2, 'Aurora Eka Marpaung', '088212421433', 'jl.sekarang', 'COD', NULL, 190000.00, NULL, 'Selesai', '2026-11-04 00:12:56'),
(3, 'FARA-261104-96B2F9', 2, 'Aurora Eka Marpaung', '088212421433', 'jl.sekarang', 'COD', NULL, 89000.00, NULL, 'Selesai', '2026-11-04 00:20:18'),
(4, 'FARA-261104-136CA0', 2, 'Aurora Eka Marpaung', '088212421433', 'jl.sekarang', 'Transfer', 'BCA', 223000.00, 'uploads/proofs/proof_6aea7a5e17ba5.png', 'Selesai', '2026-11-04 00:20:46'),
(5, 'FARA-261105-BD09CB', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2', 'COD', NULL, 144000.00, NULL, 'Selesai', '2026-11-05 00:26:31'),
(6, 'FARA-261105-06BBCE', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2', 'COD', NULL, 134000.00, NULL, 'Selesai', '2026-11-05 00:31:23'),
(7, 'FARA-261105-8AF53F', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2', 'COD', NULL, 67000.00, NULL, 'Pesanan Diproses', '2026-11-05 03:58:02'),
(8, 'FARA-261105-B40D10', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2', 'QRIS', NULL, 176000.00, 'uploads/proofs/proof_ffcdaf683c2e2440aab1468f.jpg', 'Selesai', '2026-11-05 09:05:52'),
(9, 'FARA-261106-68CE16', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2', 'COD', NULL, 176000.00, NULL, 'Selesai', '2026-11-06 04:40:21'),
(10, 'FARA-261106-5EC6AA', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2', 'Transfer', 'SeaBank', 192000.00, 'uploads/proofs/proof_b4baf2af2eace64280778090.jpg', 'Selesai', '2026-11-06 06:40:56'),
(11, 'FARA-261106-20AB82', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2 rt 2', 'COD', NULL, 172000.00, NULL, 'Pesanan Diproses', '2026-11-06 12:11:57'),
(12, 'FARA-261106-4B1048', 3, 'tami', '088212421433', 'jl. pancawarga 27 Rt.013/Rw.01 CBS Jatinegara Jakarta Timur', 'Transfer', 'SeaBank', 99000.00, 'uploads/proofs/proof_3059da7aec541573ba29bc2e.png', 'Dibatalkan', '2026-11-06 12:54:46'),
(13, 'FARA-261107-D12A47', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2 rt 2', 'COD', NULL, 179000.00, NULL, 'Selesai', '2026-11-07 05:56:26'),
(14, 'FARA-261107-DADBA1', 2, 'Aurora Eka Marpaung', '088212421433', 'Jl. Sekarang No.2 rt 2', 'COD', NULL, 201000.00, NULL, 'Pesanan Diproses', '2026-11-07 06:03:15'),
(15, 'FARA-261107-F2321F', 4, 'Yoyaaay', '089843755678', 'jl.Kemang 2 No 8 Rt.01/Rw.2 Cbs Jatner', 'Transfer', 'SeaBank', 2439000.00, 'uploads/proofs/proof_54be691cb9b1abd2e89aa78c.png', 'Selesai', '2026-11-07 06:52:14'),
(16, 'FARA-261109-C2D308', 5, 'me', '088212421433', 'jl.2 no 6 rt 23', 'COD', NULL, 67000.00, NULL, 'Dibatalkan', '2026-11-09 10:50:47'),
(17, 'FARA-261109-DC26D1', 6, 'Fazli', '08128419435', 'gg. Awab No.20A', 'QRIS', NULL, 867000.00, 'uploads/proofs/proof_bbb72a983f3ac329e37d834e.jpg', 'Dibatalkan', '2026-11-09 14:04:01'),
(18, 'FARA-261110-134909', 4, 'Yoyaaay', '089843755678', 'jl.Kemang 2 No 8 Rt.01/Rw.2 Cbs Jatner', 'COD', NULL, 176000.00, NULL, 'Selesai', '2026-11-10 03:26:00'),
(19, '101126019', 4, 'Yoyaaay', '089843755678', 'jl.Kemang 2 No 8 Rt.01/Rw.2 Cbs Jatner', 'COD', NULL, 198000.00, NULL, 'Pesanan Diproses', '2026-11-10 03:43:41'),
(20, '101126020', 4, 'Yoyaaay', '089843755678', 'jl.Kemang 2 No 8 Rt.01/Rw.2 Cbs Jatner', 'COD', NULL, 99000.00, NULL, 'Dikemas', '2026-11-10 03:43:57'),
(21, 'FARA-101126021', 4, 'Yoyaaay', '089843755678', 'jl.Kemang 2 No 8 Rt.01/Rw.2 Cbs Jatner', 'COD', NULL, 67000.00, NULL, 'Selesai', '2026-11-10 03:46:01'),
(22, 'FARA-101126022', 4, 'Yoyaaay', '089843755678', 'jl.Kemang 2 No 8 Rt.01/Rw.2 Cbs Jatner', 'COD', NULL, 67000.00, NULL, 'Pesanan Diproses', '2026-11-10 03:54:24'),
(23, 'FARA-101126023', 4, 'Yoyaaay', '089843755678', 'jl.Kemang 2 No 8 Rt.01/Rw.2 Cbs Jatner', 'COD', NULL, 5963000.00, NULL, 'Pesanan Diproses', '2026-11-10 03:55:10'),
(24, 'FARA-101126024', 7, 'RARA', '08654363773838', 'jl.mawar berduri rt.12/rw.01 Jkt Timur', 'COD', NULL, 960000.00, NULL, 'Selesai', '2026-11-10 04:03:59'),
(25, 'FARA-101126025', 7, 'RARA', '08654363773838', 'jl.mawar berduri rt.12/rw.01 Jkt Timur', 'COD', NULL, 211000.00, NULL, 'Pesanan Diproses', '2026-11-10 04:19:35'),
(26, 'FARA-101126026', 7, 'RARA', '08654363773838', 'jl.mawar berduri rt.12/rw.01 Jkt Timur', 'COD', NULL, 160000.00, NULL, 'Selesai', '2026-11-10 04:19:52'),
(27, 'FARA-101126027', 7, 'RARA', '08654363773838', 'jl.mawar berduri rt.12/rw.01 Jkt Timur', 'QRIS', NULL, 80000.00, 'uploads/proofs/proof_5508df5a0f99791f4f8d7e96.png', 'Selesai', '2026-11-10 04:29:13'),
(28, 'FARA-101126028', 7, 'RARA', '08654363773838', 'jl.mawar berduri rt.12/rw.01 Jkt Timur', 'COD', NULL, 67000.00, NULL, 'Dikirim', '2026-11-10 06:40:31'),
(29, 'FARA-101126029', 7, 'RARA', '08654363773838', 'jl.mawar berduri rt.12/rw.01 Jkt Timur', 'COD', NULL, 804000.00, NULL, 'Barang Diterima', '2026-11-10 06:40:51'),
(30, 'FARA-101126030', 8, 'Rora', '088212421433', 'jl. 2', 'COD', NULL, 99000.00, NULL, 'Pesanan Diproses', '2026-11-10 07:20:28');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `book_title` varchar(180) NOT NULL,
  `book_author` varchar(140) NOT NULL,
  `book_image` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `book_id`, `book_title`, `book_author`, `book_image`, `price`, `quantity`) VALUES
(1, 1, 1, 'Laut Bercerita', 'Leila S. Chudori', 'assets/covers/laut.svg', 98000.00, 1),
(2, 1, 2, 'Pulang', 'Tere Liye', 'assets/covers/pulang.svg', 89000.00, 1),
(3, 2, 4, 'Bumi', 'Tere Liye', 'assets/covers/bumi.svg', 95000.00, 2),
(4, 3, 2, 'Pulang', 'Tere Liye', 'assets/covers/pulang.svg', 89000.00, 1),
(5, 4, NULL, 'Atomic Habits', 'James Clear', 'assets/covers/atomic.svg', 115000.00, 1),
(6, 4, 7, 'The Midnight Library', 'Matt Haig', 'assets/covers/midnight.svg', 108000.00, 1),
(7, 5, 18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'assets/uploads/books/9a76060dc1b885e5764ec959646206b0.jpg', 77000.00, 1),
(8, 5, 17, 'Yang Telah Lama Pergi', 'Tere Liye', 'assets/uploads/books/2f0bc8129848f6f2e82eb74d875bdc62.jpg', 67000.00, 1),
(9, 6, 17, 'Yang Telah Lama Pergi', 'Tere Liye', 'assets/uploads/books/2f0bc8129848f6f2e82eb74d875bdc62.jpg', 67000.00, 2),
(10, 7, 17, 'Yang Telah Lama Pergi', 'Tere Liye', 'assets/uploads/books/10f9b3eb3a50258ad8e2e00fb9b8aa8f.jpg', 67000.00, 1),
(11, 8, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 1),
(12, 8, 18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'assets/uploads/books/5ebc0ad49839cb267e6536d1d0d74372.jpg', 77000.00, 1),
(13, 9, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 1),
(14, 9, 18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'assets/uploads/books/5ebc0ad49839cb267e6536d1d0d74372.jpg', 77000.00, 1),
(15, 10, 18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'assets/uploads/books/5ebc0ad49839cb267e6536d1d0d74372.jpg', 77000.00, 1),
(16, 10, 13, 'Rindu yang Tak pernah Sirna', 'Nur Diyafiqa', 'assets/uploads/books/e5d91ed067ddbb9ee40fa8810d0c3f17.jpg', 115000.00, 1),
(17, 11, 4, 'Bumi', 'Tere Liye', 'assets/uploads/books/5c2e05ed477712d9a89622faac2181b4.jpg', 95000.00, 1),
(18, 11, 18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'assets/uploads/books/5ebc0ad49839cb267e6536d1d0d74372.jpg', 77000.00, 1),
(19, 12, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 1),
(20, 13, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 1),
(21, 13, 20, 'Tentang Kamu', 'Tere Liye', 'assets/uploads/books/2d7bcc632a72706643270028cbebec43.jpg', 80000.00, 1),
(22, 14, 22, 'Kala Itu Langit Biru', 'Kim Chun Hei', 'assets/uploads/books/4c7f4e8dafc92007a3901cc1db0e6b53.jpg', 67000.00, 3),
(23, 15, 22, 'Kala Itu Langit Biru', 'Kim Chun Hei', 'assets/uploads/books/4c7f4e8dafc92007a3901cc1db0e6b53.jpg', 67000.00, 1),
(24, 15, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 4),
(25, 15, 18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'assets/uploads/books/5ebc0ad49839cb267e6536d1d0d74372.jpg', 77000.00, 10),
(26, 15, 17, 'Yang Telah Lama Pergi', 'Tere Liye', 'assets/uploads/books/10f9b3eb3a50258ad8e2e00fb9b8aa8f.jpg', 67000.00, 18),
(27, 16, 17, 'Yang Telah Lama Pergi', 'Tere Liye', 'assets/uploads/books/10f9b3eb3a50258ad8e2e00fb9b8aa8f.jpg', 67000.00, 1),
(28, 17, 20, 'Tentang Kamu', 'Tere Liye', 'assets/uploads/books/2d7bcc632a72706643270028cbebec43.jpg', 80000.00, 10),
(29, 17, 22, 'Kala Itu Langit Biru', 'Kim Chun Hei', 'assets/uploads/books/4c7f4e8dafc92007a3901cc1db0e6b53.jpg', 67000.00, 1),
(30, 18, 18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'assets/uploads/books/5ebc0ad49839cb267e6536d1d0d74372.jpg', 77000.00, 1),
(31, 18, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 1),
(32, 19, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 2),
(33, 20, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 1),
(34, 21, 17, 'Yang Telah Lama Pergi', 'Tere Liye', 'assets/uploads/books/10f9b3eb3a50258ad8e2e00fb9b8aa8f.jpg', 67000.00, 1),
(35, 22, 22, 'Kala Itu Langit Biru', 'Kim Chun Hei', 'assets/uploads/books/4c7f4e8dafc92007a3901cc1db0e6b53.jpg', 67000.00, 1),
(36, 23, 22, 'Kala Itu Langit Biru', 'Kim Chun Hei', 'assets/uploads/books/4c7f4e8dafc92007a3901cc1db0e6b53.jpg', 67000.00, 89),
(37, 24, 20, 'Tentang Kamu', 'Tere Liye', 'assets/uploads/books/2d7bcc632a72706643270028cbebec43.jpg', 80000.00, 12),
(38, 25, 17, 'Yang Telah Lama Pergi', 'Tere Liye', 'assets/uploads/books/10f9b3eb3a50258ad8e2e00fb9b8aa8f.jpg', 67000.00, 2),
(39, 25, 18, 'Hilang Dalam Dekapan Semeru', 'RJ5-Fajar Aditya', 'assets/uploads/books/5ebc0ad49839cb267e6536d1d0d74372.jpg', 77000.00, 1),
(40, 26, 20, 'Tentang Kamu', 'Tere Liye', 'assets/uploads/books/2d7bcc632a72706643270028cbebec43.jpg', 80000.00, 2),
(41, 27, 20, 'Tentang Kamu', 'Tere Liye', 'assets/uploads/books/2d7bcc632a72706643270028cbebec43.jpg', 80000.00, 1),
(42, 28, 22, 'Kala Itu Langit Biru', 'Kim Chun Hei', 'assets/uploads/books/4c7f4e8dafc92007a3901cc1db0e6b53.jpg', 67000.00, 1),
(43, 29, 22, 'Kala Itu Langit Biru', 'Kim Chun Hei', 'assets/uploads/books/4c7f4e8dafc92007a3901cc1db0e6b53.jpg', 67000.00, 12),
(44, 30, 19, 'Ternyata Menjadi Dewasa Itu...', 'Yoga Maulana', 'assets/uploads/books/7ed821b44cb3d69df8eabcbb2d03bb6d.jpg', 99000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_returns`
--

CREATE TABLE `order_returns` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `evidence_path` varchar(255) NOT NULL,
  `evidence_type` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu Review',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `password`, `created_at`) VALUES
(1, 'farah', 'farah22@gmail.com', '08568331354', 'jl. lpn 11 no 2 rt 4 rw 7 cbs', '$2y$10$/GEhYX5bF4PxywqdXwyRDeHTOl21pbsVGKYMJmWqWBaR57QffAHcS', '2026-11-03 02:27:48'),
(2, 'Aurora Eka Marpaung', 'nothingrara@gmail.com', '088212421433', 'Jl. Sekarang No.2 rt 2', '$2y$10$Ja58hvAyzhSSle0hLozXEewiK6p67QI9WmbSfhwGAzdABdXqUasii', '2026-11-04 00:10:17'),
(3, 'tami', 'rustami@gmail.com', '088212421433', 'jl. pancawarga 27 Rt.013/Rw.01 CBS Jatinegara Jakarta Timur', '$2y$10$9BkzmN2WklqFTnBIyw5FaeV/etsGjw8.o34B8wflcpgAqlWhwXRWG', '2026-11-06 12:52:47'),
(4, 'Yoyaaay', 'yoya12@gmail.com', '089843755678', 'jl.Kemang 2 No 8 Rt.01/Rw.2 Cbs Jatner', '$2y$10$kOHAIonG9lrQ0nA2fTJusevTgR8Pj9BMxOsAyH0sizawBejOth2eG', '2026-11-07 06:50:00'),
(5, 'me', 'me8@gmail.com', '', '', '$2y$10$CABRTDgGpTuLySmQpCt8MeF3KsN8VyFKge3O8NqjEOoJSSM8A76bK', '2026-11-09 10:43:53'),
(6, 'Fazli', 'fazli123@gmail.com', '08128419435', 'gg. Awab No.20A', '$2y$10$WqFeWPhO5ezQF2hopx4dn.xD/E4GnzSHe4QBHQkwozxvHm4feu4t6', '2026-11-09 14:00:07'),
(7, 'RARA', 'rara12@gmail.com', '08654363773838', 'jl.mawar berduri rt.12/rw.01 Jkt Timur', '$2y$10$vSjuix1oUkVlJqk3EiQ4veS5PTvgGSF97VgW24P01pIltYo4quiiq', '2026-11-10 04:03:14'),
(8, 'Rora', 'rora12@gmail.com', '088212421433', 'jl. 2', '$2y$10$W8m4zE8IyZpR7pZcEhFZfeH3F.bu6XA42eiOK7SM1F.3BoXofijny', '2026-11-10 07:12:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_book` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_thread_id` (`thread_id`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `message_threads`
--
ALTER TABLE `message_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_updated_at` (`updated_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_returns`
--
ALTER TABLE `order_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `idx_return_user` (`user_id`),
  ADD KEY `idx_return_order` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `message_threads`
--
ALTER TABLE `message_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `order_returns`
--
ALTER TABLE `order_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
