<?php

/**
 * DEBUG SCRIPT — Cek nilai RGB hasil konversi PDF
 *
 * Cara pakai:
 * 1. Copy file ini ke root project Laravel kamu
 * 2. Jalankan: php debug_pink.php
 * 3. Lihat output — akan terlihat nilai RGB piksel pink dan threshold yang dibutuhkan
 */

require __DIR__ . '/vendor/autoload.php';

use Spatie\PdfToImage\Pdf;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

// ── Ganti dengan path PDF kamu yang mengandung e-materai ──
$pdfPath = __DIR__ . '/storage/app/private/materai/original/NAMA_FILE.pdf';

if (!file_exists($pdfPath)) {
    // Cari file PDF terbaru di folder original
    $files = glob(__DIR__ . '/storage/app/private/materai/original/*.pdf');
    if (empty($files)) {
        die("❌ Tidak ada file PDF di storage/app/private/materai/original/\n   Upload dulu file PDF via Postman, lalu jalankan script ini.\n");
    }
    // Ambil yang terbaru
    usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
    $pdfPath = $files[0];
}

echo "📄 File PDF   : " . basename($pdfPath) . "\n";

// ── Konversi halaman terakhir ke PNG ──
$tmpDir = __DIR__ . '/storage/app/private/materai/tmp';
if (!is_dir($tmpDir))
    mkdir($tmpDir, 0755, true);

$pdf = new Pdf($pdfPath);
$totalPages = $pdf->getNumberOfPages();
echo "   Total halaman: {$totalPages}\n";

$pdf->setResolution(200);
$pdf->setPage($totalPages);

// Test dua format: PNG dan JPEG
$pngPath = $tmpDir . '/debug_output.png';
$jpegPath = $tmpDir . '/debug_output.jpg';

$pdf->saveImage($pngPath);
$pdf->saveImage($jpegPath);

echo "\n";

// ── Analisis kedua file ──
foreach ([
    'PNG (lossless)' => $pngPath,
    'JPEG (lossy)' => $jpegPath,
] as $label => $path) {

    if (!file_exists($path)) {
        echo "❌ {$label}: gagal dibuat\n";

        // Coba cek apakah Spatie ganti ekstensi otomatis
        $altPng = str_replace('.png', '.jpg', $path);
        $altJpeg = str_replace('.jpg', '.png', $path);
        if (file_exists($altPng))
            echo "   ⚠️  Spatie simpan sebagai .jpg meski diminta .png: {$altPng}\n";
        if (file_exists($altJpeg))
            echo "   ⚠️  Spatie simpan sebagai .png meski diminta .jpg: {$altJpeg}\n";
        continue;
    }

    echo "✅ {$label}: " . basename($path) . " (" . round(filesize($path) / 1024) . " KB)\n";

    // Buka dengan GD
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $gd = $ext === 'png' ? @imagecreatefrompng($path) : @imagecreatefromjpeg($path);
    if (!$gd) {
        echo "   ❌ GD gagal buka file ini\n";
        continue;
    }

    $w = imagesx($gd);
    $h = imagesy($gd);
    echo "   Dimensi: {$w} x {$h} px\n";

    // Scan seluruh gambar, kumpulkan piksel pink
    $totalSampled = 0;
    $pinkPixels = [];

    for ($x = 0; $x < $w; $x += 3) {
        for ($y = 0; $y < $h; $y += 3) {
            $color = imagecolorat($gd, $x, $y);
            $r = ($color >> 16) & 0xFF;
            $g = ($color >> 8) & 0xFF;
            $b = $color & 0xFF;
            $totalSampled++;

            // Kriteria longgar — tangkap semua yang "mungkin pink"
            if ($r > 120 && $g < 180 && $b > 60 && $r > $g && $r > $b) {
                $pinkPixels[] = [$r, $g, $b, $x, $y];
            }
        }
    }

    echo "   Total piksel di-sample  : {$totalSampled}\n";
    echo "   Piksel kandidat pink     : " . count($pinkPixels) . "\n";
    echo "   Rasio                    : " . round(count($pinkPixels) / $totalSampled * 100, 4) . "%\n";

    if (!empty($pinkPixels)) {
        // Ambil sampel 10 piksel pink untuk lihat nilai RGB-nya
        echo "\n   Sampel nilai RGB piksel pink:\n";
        $sample = array_slice($pinkPixels, 0, min(10, count($pinkPixels)));
        foreach ($sample as [$r, $g, $b, $x, $y]) {
            echo "     rgb({$r}, {$g}, {$b}) di koordinat ({$x}, {$y})\n";
        }

        // Cek apakah lolos kriteria kode controller
        $lolos = 0;
        foreach ($pinkPixels as [$r, $g, $b]) {
            if ($r > 140 && $g < 150 && $b > 80 && $r > $g + 30 && $r > $b) {
                $lolos++;
            }
        }
        $ratioLolos = $lolos / $totalSampled;
        echo "\n   Lolos kriteria controller : {$lolos} piksel\n";
        echo "   Rasio lolos              : " . round($ratioLolos * 100, 4) . "%\n";
        echo "   Threshold saat ini (0.3%): " . ($ratioLolos > 0.003 ? "✅ TERDETEKSI ELEKTRONIK" : "❌ TIDAK TERDETEKSI — threshold terlalu tinggi") . "\n";

        if ($ratioLolos <= 0.003 && $lolos > 0) {
            $needed = ceil($totalSampled * 0.003);
            $threshold = round($lolos / $totalSampled, 6);
            echo "\n   💡 Solusi: ganti threshold dari 0.003 menjadi {$threshold}\n";
            echo "      Ubah baris: return \$ratio > 0.003;\n";
            echo "      Menjadi   : return \$ratio > {$threshold};\n";
        }
    } else {
        echo "\n   ⚠️  Tidak ada piksel pink terdeteksi sama sekali.\n";
        echo "      Kemungkinan: gambar grayscale, warna berubah saat konversi,\n";
        echo "      atau e-materai tidak ada di halaman ini.\n";

        // Cek apakah gambar grayscale
        $colorSample = [];
        for ($x = 0; $x < min($w, 100); $x += 5) {
            for ($y = 0; $y < min($h, 100); $y += 5) {
                $color = imagecolorat($gd, $x, $y);
                $r = ($color >> 16) & 0xFF;
                $g = ($color >> 8) & 0xFF;
                $b = $color & 0xFF;
                if (abs($r - $g) > 10 || abs($g - $b) > 10) {
                    $colorSample[] = "rgb({$r},{$g},{$b})";
                }
            }
        }
        if (empty($colorSample)) {
            echo "      ❌ Gambar terlihat GRAYSCALE — Spatie mungkin konversi ke grayscale\n";
            echo "         Coba tambahkan: \$pdf->setColorspace('rgb');\n";
        } else {
            echo "      ✅ Gambar berwarna, tapi tidak ada pink. Sample warna:\n";
            foreach (array_slice($colorSample, 0, 5) as $c) {
                echo "         {$c}\n";
            }
        }
    }

    imagedestroy($gd);
    echo "\n";
}

echo "─────────────────────────────────────────\n";
echo "File debug tersimpan di:\n";
echo "  PNG : {$pngPath}\n";
echo "  JPEG: {$jpegPath}\n";
echo "Buka kedua file di image viewer untuk cek tampilan visualnya.\n";