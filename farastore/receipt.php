<?php
/**
 * FARASTORE - STRUK PEMBAYARAN PDF
 *
 * Membuat file PDF struk secara langsung dengan PHP.
 * Tidak memerlukan Composer atau library PDF tambahan.
 */

require_once __DIR__ . '/config.php';
require_login();

// Ambil pesanan milik user yang sedang login saja.
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId) {
    http_response_code(400);
    exit('Nomor pesanan tidak valid.');
}

$orderQuery = $pdo->prepare(
    'SELECT *
     FROM orders
     WHERE id = ? AND user_id = ?
     LIMIT 1'
);

$orderQuery->execute([$orderId, $_SESSION['user_id']]);
$order = $orderQuery->fetch();

if (!$order) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan.');
}

// Ambil rincian item pesanan.
$itemQuery = $pdo->prepare(
    'SELECT book_title, book_author, price, quantity
     FROM order_items
     WHERE order_id = ?
     ORDER BY id ASC'
);

$itemQuery->execute([$orderId]);
$items = $itemQuery->fetchAll();

/**
 * Mengubah teks menjadi karakter aman untuk font Helvetica PDF.
 */
function pdf_text($value)
{
    $value = (string) $value;

    if (function_exists('iconv')) {
        $converted = iconv(
            'UTF-8',
            'Windows-1252//TRANSLIT//IGNORE',
            $value
        );

        if ($converted !== false) {
            $value = $converted;
        }
    }

    $value = preg_replace('/[^\x20-\x7E\x80-\xFF]/', '', $value);

    return str_replace(
        ['\\', '(', ')'],
        ['\\\\', '\\(', '\\)'],
        $value
    );
}

/**
 * Format Rupiah untuk isi struk.
 */
function receipt_rupiah($amount)
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

/**
 * Membuat satu baris teks PDF.
 */
function pdf_line($text, $x, $y, $fontSize = 10, $font = 'F1')
{
    return "BT /{$font} {$fontSize} Tf 1 0 0 1 {$x} {$y} Tm ("
        . pdf_text($text)
        . ") Tj ET\n";
}

$stream = '';
$y = 790;

// Header struk.
$stream .= pdf_line('FARASTORE', 50, $y, 22, 'F2');
$y -= 22;
$stream .= pdf_line('STRUK PEMBAYARAN / BUKTI TRANSAKSI', 50, $y, 10, 'F2');
$y -= 18;
$stream .= "0.85 0.55 0.67 RG 1 w 50 {$y} m 545 {$y} l S\n";
$y -= 25;

// Informasi pesanan.
$stream .= pdf_line('Kode pesanan : ' . $order['order_code'], 50, $y, 10);
$y -= 17;
$stream .= pdf_line(
    'Tanggal      : ' . date('d-m-Y H:i', strtotime($order['created_at'])),
    50,
    $y
);
$y -= 17;
$stream .= pdf_line('Status       : ' . $order['status'], 50, $y);
$y -= 17;
$stream .= pdf_line('Pelanggan    : ' . $order['customer_name'], 50, $y);
$y -= 17;
$stream .= pdf_line('Telepon      : ' . $order['phone'], 50, $y);
$y -= 17;

// Alamat dapat terdiri dari beberapa baris.
$address = preg_split('/\r\n|\r|\n/', trim($order['address']));
$stream .= pdf_line('Alamat       : ' . ($address[0] ?? '-'), 50, $y);
$y -= 15;

foreach (array_slice($address, 1) as $addressLine) {
    $stream .= pdf_line($addressLine, 112, $y);
    $y -= 14;
}

$y -= 10;
$stream .= "0.85 0.55 0.67 RG 1 w 50 {$y} m 545 {$y} l S\n";
$y -= 22;

// Header tabel item.
$stream .= pdf_line('DAFTAR BUKU', 50, $y, 11, 'F2');
$y -= 22;
$stream .= pdf_line('Nama buku', 50, $y, 9, 'F2');
$stream .= pdf_line('Qty', 350, $y, 9, 'F2');
$stream .= pdf_line('Harga', 395, $y, 9, 'F2');
$stream .= pdf_line('Subtotal', 475, $y, 9, 'F2');
$y -= 8;
$stream .= "0.75 0.75 0.75 RG 0.5 w 50 {$y} m 545 {$y} l S\n";
$y -= 18;

// Baris buku.
foreach ($items as $item) {
    if ($y < 105) {
        // Batasi agar struk tetap satu halaman yang mudah dibaca.
        $stream .= pdf_line('... rincian selanjutnya tersedia pada halaman detail pesanan.', 50, $y, 8);
        break;
    }

    $bookTitle = $item['book_title'];
    if (strlen($bookTitle) > 42) {
        $bookTitle = substr($bookTitle, 0, 39) . '...';
    }

    $stream .= pdf_line($bookTitle, 50, $y, 8);
    $stream .= pdf_line((string) $item['quantity'], 355, $y, 8);
    $stream .= pdf_line(receipt_rupiah($item['price']), 395, $y, 8);
    $stream .= pdf_line(
        receipt_rupiah($item['price'] * $item['quantity']),
        475,
        $y,
        8
    );

    $y -= 18;
}

// Total dan metode pembayaran.
$y -= 5;
$stream .= "0.85 0.55 0.67 RG 1 w 50 {$y} m 545 {$y} l S\n";
$y -= 25;
$stream .= pdf_line('Metode pembayaran : ' . $order['payment_method'], 50, $y, 9);
$y -= 17;

if (!empty($order['bank_name'])) {
    $stream .= pdf_line('Bank              : ' . $order['bank_name'], 50, $y, 9);
    $y -= 17;
}

$stream .= pdf_line('TOTAL PEMBAYARAN', 350, $y, 10, 'F2');
$stream .= pdf_line(receipt_rupiah($order['total']), 455, $y - 20, 12, 'F2');
$y -= 55;

// Catatan bukti pembayaran.
if (!empty($order['payment_proof'])) {
    $stream .= pdf_line('Bukti pembayaran telah diunggah dan tersimpan pada transaksi ini.', 50, $y, 8);
} else {
    $stream .= pdf_line('Pembayaran COD: lakukan pembayaran saat pesanan diterima.', 50, $y, 8);
}

$y -= 30;
$stream .= pdf_line('Terima kasih telah berbelanja di Farastore ♡', 50, $y, 10, 'F2');
$stream .= pdf_line('Simpan struk ini sebagai bukti transaksi.', 50, $y - 16, 8);

// Susun objek PDF.
$objects = [];
$objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
$objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
$objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
    . '/Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>';
$objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
$objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
$objects[] = '<< /Length ' . strlen($stream) . " >>\nstream\n"
    . $stream . "endstream";

$pdf = "%PDF-1.4\n";
$offsets = [0];

foreach ($objects as $index => $object) {
    $objectNumber = $index + 1;
    $offsets[$objectNumber] = strlen($pdf);
    $pdf .= $objectNumber . " 0 obj\n" . $object . "\nendobj\n";
}

$xrefPosition = strlen($pdf);
$pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
$pdf .= "0000000000 65535 f \n";

for ($i = 1; $i <= count($objects); $i++) {
    $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
}

$pdf .= "trailer\n<< /Size " . (count($objects) + 1)
    . " /Root 1 0 R >>\n";
$pdf .= "startxref\n" . $xrefPosition . "\n%%EOF";

$fileName = 'Struk-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $order['order_code']) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, max-age=0, must-revalidate');

echo $pdf;
exit;
