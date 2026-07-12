<?php

namespace App\Http\services;

use App\Models\Materai;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Http;
use Spatie\PdfToImage\Pdf;

/**
 * MateraiOcrService
 * ──────────────────────────────────────────────────────────────────────
 * Service ini berisi seluruh pipeline pemrosesan dokumen materai:
 *   PDF/Gambar → (deteksi e-materai / OCR materai fisik) → hasil analisis
 *
 * Diekstrak dari MateraiController agar logic yang sama dapat dipakai
 * baik oleh REST API (MateraiController) maupun oleh UI Dashboard
 * (Web\UploadController), tanpa duplikasi kode.
 * ──────────────────────────────────────────────────────────────────────
 */
class MateraiOcrService
{
    /**
     * Proses sebuah file upload (PDF/JPG/PNG) dari awal sampai akhir:
     * simpan file asli, convert PDF → gambar, deteksi e-materai / OCR,
     * cek duplikasi, dan kembalikan array hasil analisis siap pakai.
     *
     * Struktur return-nya identik dengan response data pada
     * MateraiController::upload(), sehingga bisa dipakai langsung
     * oleh API maupun ditampilkan di view Blade.
     */
    /**
     * Analisis dokumen e-Materai.
     * Alur:
     * 1. Simpan file.
     * 2. Konversi PDF ke gambar (jika diperlukan).
     * 3. Buat preview.
     * 4. Kirim gambar ke FastAPI.
     * 5. Terima hasil YOLO + PaddleOCR.
     * 6. Cek duplikasi nomor seri.
     * 7. Kembalikan hasil analisis.
     */
    public function analyze(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $uid = uniqid('mat_', true);
        $filename = $uid . '.' . $ext;

        // ── Simpan file asli ke storage private ──
        $storedPath = $file->storeAs('materai/original', $filename, 'public');
        $fullPath = Storage::disk('public')->path($storedPath);

        // ── PDF → ambil halaman terakhir ──
        $imagePath = $fullPath;
        if ($ext === 'pdf') {
            $imagePath = $this->pdfLastPageToImage($fullPath, $uid);
        }

        if (!$imagePath || !file_exists($imagePath)) {
            return [
                'success' => false,
                'message' => 'Gagal membaca file. Pastikan file tidak rusak.',
            ];
        }

        // ── Buat preview publik ──
        $previewRelative = $this->makePublicPreview($imagePath, $uid);

        $response = Http::timeout(120)
            ->attach(
                'file',
                fopen($imagePath, 'r'),
                basename($imagePath)
            )
            ->post('http://127.0.0.1:8001/detect');

        $result = $response->json();

        // ── Ambil langsung dari response FastAPI, jangan hitung ulang ──
        // jenis bisa: 'elektronik', 'fisik', atau null (tidak terdeteksi)
        $jenis = $result['jenis'] ?? null;
        $nomorSeri = $result['nomor_seri'] ?? null;
        $rawText = $result['raw_text'] ?? '';
        $cropImage = $result['crop_image'] ?? null;
        $confidence = ($result['confidence_ocr'] ?? 0) * 100;

        $status = 'tidak_terbaca';
        $duplikatInfo = null;

        // ── Cek duplikat hanya berlaku untuk materai fisik (punya nomor seri) ──
        if ($jenis === 'fisik' && $nomorSeri) {

            $existing = Materai::where('nomor_seri', $nomorSeri)->first();

            if ($existing) {
                $status = 'duplikat';

                $duplikatInfo = [
                    'id' => $existing->id,
                    'nomor_seri' => $existing->nomor_seri,
                    'nama_file' => $existing->nama_file,
                    'uploaded_by' => $existing->apiClient?->nama ?? 'tidak diketahui',
                    'tanggal' => $existing->created_at?->format('d M Y H:i'),
                ];
            } else {
                $status = 'unik';
            }

        } elseif ($jenis === 'elektronik') {
            // E-materai tidak punya nomor seri yang bisa dicek duplikat
            $status = 'tidak_terbaca';

        } else {
            // $jenis === null → materai tidak terdeteksi sama sekali
            $status = 'tidak_terbaca';
        }

        return [
            'success' => true,
            'data' => [
                'temp_id' => $uid,
                'nomor_seri' => $nomorSeri,
                'jenis' => $jenis,
                'status' => $status,
                'confidence' => $confidence,
                'raw_text' => $rawText,
                'image_url' => asset($previewRelative),
                'stored_path' => $storedPath,
                'nama_file' => $file->getClientOriginalName(),
                'duplikat_info' => $duplikatInfo,
                'qr_image_url' => $cropImage,
            ],
        ];
    }

    /**
     * Simpan hasil analisis (array dari analyze()) ke tabel materai.
     * Melakukan pengecekan duplikasi tahap kedua (final) sebelum simpan,
     * karena nomor_seri mungkin telah dikoreksi manual oleh pengguna.
     *
     * $apiClientId boleh null (misalnya untuk upload uji coba dari dashboard admin).
     */
    public function store(array $payload, ?int $apiClientId): Materai
    {
        $nomorSeri = $payload['nomor_seri'] ?: null;
        $status = $payload['status'];
        $duplikatDariId = null;

        if ($nomorSeri && $status !== 'tidak_terbaca') {
            $existing = Materai::where('nomor_seri', $nomorSeri)->first();
            if ($existing) {
                $status = 'duplikat';
                $duplikatDariId = $existing->id;
                $existing->increment('duplikat_count');
            } else {
                $status = 'unik';
            }
        }

        return Materai::create([
            'api_client_id' => $apiClientId,
            'nomor_seri' => $nomorSeri,
            'jenis' => $payload['jenis'] ?? null,
            'nama_file' => $payload['nama_file'],
            'path_file' => $payload['stored_path'] ?? $payload['path_file'],
            'status' => $status,
            'duplikat_dari_id' => $duplikatDariId,
            'confidence' => $payload['confidence'] ?? null,
            'raw_text' => $payload['raw_text'] ?? null,
            'qr_image_url' => $payload['qr_image_url'] ?? null,
        ]);
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE HELPER METHODS (dipindah apa adanya dari MateraiController)
    // ════════════════════════════════════════════════════

    private function pdfLastPageToImage(string $pdfPath, string $uid): ?string
    {
        try {
            $pdf = new Pdf($pdfPath);
            $totalPages = $pdf->getNumberOfPages();

            $pdf->setResolution(200);
            $pdf->setPage($totalPages);

            $outPath = storage_path("app/private/materai/tmp/{$uid}_page.jpg");
            $this->ensureDir(dirname($outPath));
            $pdf->saveImage($outPath);

            return file_exists($outPath) ? $outPath : null;

        } catch (\Throwable $e) {
            Log::error('pdfLastPageToImage gagal', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function makePublicPreview(string $imagePath, string $uid): string
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->read($imagePath);

        if ($img->width() > 1280) {
            $img->scale(width: 1280);
        }

        $relative = "materai/preview/{$uid}_preview.jpg";
        $outPath = storage_path("app/public/{$relative}");
        $this->ensureDir(dirname($outPath));

        $img->save($outPath, quality: 85);

        return 'storage/' . $relative;
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
