
<?php
require_once '../config.php';
require_admin();

/*
|--------------------------------------------------------------------------
| 1. Ambil ID pesanan
|--------------------------------------------------------------------------
*/
$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    exit('ID pesanan tidak valid.');
}

/*
|--------------------------------------------------------------------------
| 2. Ambil data bukti pembayaran
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT id, order_code, payment_proof
    FROM orders
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('Pesanan tidak ditemukan.');
}

if (empty($order['payment_proof'])) {
    exit('Pesanan ini belum memiliki bukti pembayaran.');
}

/*
|--------------------------------------------------------------------------
| 3. Periksa lokasi file
|--------------------------------------------------------------------------
| File bukti pembayaran disimpan di:
| uploads/proofs/
*/
$proofPath = str_replace('\\', '/', $order['payment_proof']);

if (
    strpos($proofPath, 'uploads/proofs/') !== 0 ||
    strpos($proofPath, '..') !== false
) {
    exit('Lokasi bukti pembayaran tidak valid.');
}

$rootDirectory = realpath(__DIR__ . '/..');
$imageFile = realpath($rootDirectory . '/' . $proofPath);

if (
    !$imageFile ||
    strpos($imageFile, $rootDirectory . DIRECTORY_SEPARATOR) !== 0 ||
    !is_file($imageFile)
) {
    exit('File bukti pembayaran tidak ditemukan.');
}

/*
|--------------------------------------------------------------------------
| 4. Baca dan periksa gambar
|--------------------------------------------------------------------------
*/
$imageInfo = getimagesize($imageFile);

if (!$imageInfo) {
    exit('File bukan gambar yang valid.');
}

$mime = $imageInfo['mime'];

if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    exit('Format gambar tidak didukung.');
}

$imageData = file_get_contents($imageFile);

if ($imageData === false) {
    exit('Gagal membaca gambar.');
}

/*
|--------------------------------------------------------------------------
| 5. Ubah gambar menjadi JPEG
|--------------------------------------------------------------------------
| JPEG dapat dimasukkan langsung ke PDF.
| PNG dan WebP dikonversi menggunakan ekstensi GD.
*/
if ($mime !== 'image/jpeg') {
    if (!extension_loaded('gd')) {
        exit('Ekstensi PHP GD harus diaktifkan untuk mengubah PNG/WebP menjadi PDF.');
    }

    $sourceImage = imagecreatefromstring($imageData);

    if (!$sourceImage) {
        exit('Gagal memproses gambar.');
    }

    $width = imagesx($sourceImage);
    $height = imagesy($sourceImage);

    $jpegImage = imagecreatetruecolor($width, $height);

    // Latar putih agar bagian transparan tidak menjadi hitam.
    $white = imagecolorallocate($jpegImage, 255, 255, 255);
    imagefill($jpegImage, 0, 0, $white);

    imagecopy(
        $jpegImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $width,
        $height
    );

    ob_start();
    imagejpeg($jpegImage, null, 90);
    $imageData = ob_get_clean();

    imagedestroy($sourceImage);
    imagedestroy($jpegImage);
}

$imageInfo = getimagesizefromstring($imageData);

if (!$imageInfo) {
    exit('Gambar gagal dikonversi.');
}

$imageWidth = $imageInfo[0];
$imageHeight = $imageInfo[1];

/*
|--------------------------------------------------------------------------
| 6. Atur ukuran gambar pada halaman A4
|--------------------------------------------------------------------------
*/
$pageWidth = 595.28;
$pageHeight = 841.89;
$margin = 36;

$availableWidth = $pageWidth - ($margin * 2);
$availableHeight = $pageHeight - ($margin * 2);

$scale = min(
    $availableWidth / $imageWidth,
    $availableHeight / $imageHeight
);

$drawWidth = $imageWidth * $scale;
$drawHeight = $imageHeight * $scale;

$drawX = ($pageWidth - $drawWidth) / 2;
$drawY = ($pageHeight - $drawHeight) / 2;

/*
|--------------------------------------------------------------------------
| 7. Buat isi halaman PDF
|--------------------------------------------------------------------------
*/
$content = "q\n";
$content .= sprintf(
    "%.3f 0 0 %.3f %.3f %.3f cm\n",
    $drawWidth,
    $drawHeight,
    $drawX,
    $drawY
);
$content .= "/Im0 Do\n";
$content .= "Q\n";

/*
|--------------------------------------------------------------------------
| 8. Susun objek PDF
|--------------------------------------------------------------------------
*/
$objects = [];

$objects[] = '<< /Type /Catalog /Pages 2 0 R >>';

$objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';

$objects[] =
    '<< /Type /Page /Parent 2 0 R ' .
    '/MediaBox [0 0 ' . $pageWidth . ' ' . $pageHeight . '] ' .
    '/Resources << /XObject << /Im0 4 0 R >> >> ' .
    '/Contents 5 0 R >>';

$objects[] =
    '<< /Type /XObject /Subtype /Image ' .
    '/Width ' . $imageWidth . ' ' .
    '/Height ' . $imageHeight . ' ' .
    '/ColorSpace /DeviceRGB ' .
    '/BitsPerComponent 8 ' .
    '/Filter /DCTDecode ' .
    '/Length ' . strlen($imageData) . " >>\n" .
    "stream\n" .
    $imageData . "\n" .
    "endstream";

$objects[] =
    '<< /Length ' . strlen($content) . " >>\n" .
    "stream\n" .
    $content .
    "endstream";

/*
|--------------------------------------------------------------------------
| 9. Gabungkan objek dan buat tabel referensi PDF
|--------------------------------------------------------------------------
*/
$pdf = "%PDF-1.4\n";
$pdf .= "%\xE2\xE3\xCF\xD3\n";

$offsets = [0];

foreach ($objects as $index => $object) {
    $objectNumber = $index + 1;

    $offsets[$objectNumber] = strlen($pdf);

    $pdf .= $objectNumber . " 0 obj\n";
    $pdf .= $object . "\n";
    $pdf .= "endobj\n";
}

$xrefPosition = strlen($pdf);

$pdf .= "xref\n";
$pdf .= "0 " . (count($objects) + 1) . "\n";
$pdf .= "0000000000 65535 f \n";

for ($i = 1; $i <= count($objects); $i++) {
    $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
}

$pdf .= "trailer\n";
$pdf .= "<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
$pdf .= "startxref\n";
$pdf .= $xrefPosition . "\n";
$pdf .= "%%EOF";

/*
|--------------------------------------------------------------------------
| 10. Kirim file PDF ke browser
|--------------------------------------------------------------------------
*/
$safeOrderCode = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    $order['order_code']
);

$fileName = 'Bukti-Pembayaran-' . $safeOrderCode . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store, no-cache, must-revalidate');

echo $pdf;
exit;
