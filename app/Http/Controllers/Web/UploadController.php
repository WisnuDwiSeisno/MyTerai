<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\services\MateraiOcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Web\UploadController
 * ──────────────────────────────────────────────────────────────────────
 * Halaman "Uji Coba Deteksi" di Dashboard Admin.
 * Memakai pipeline yang sama (MateraiOcrService) dengan
 * MateraiController::upload() / simpan() pada REST API, sehingga hasil
 * yang ditampilkan di UI identik dengan hasil yang diterima API client.
 *
 * Alur 2 langkah, sama seperti API:
 *   1) preview()  → analisis dokumen, tampilkan hasil pratinjau (belum disimpan)
 *   2) store()    → simpan hasil pratinjau ke tabel materai (final)
 * ──────────────────────────────────────────────────────────────────────
 */
class UploadController extends Controller
{
    public function __construct(private MateraiOcrService $ocrService)
    {
    }

    public function create()
    {
        return view('dashboard.upload', [
            'result' => session('upload_result'),
        ]);
    }

    /**
     * Tahap 1: upload & analisis (pratinjau).
     * Hasil disimpan sementara di session agar bisa dikonfirmasi pada
     * langkah berikutnya tanpa perlu upload ulang.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $result = $this->ocrService->analyze($request->file('file'));

        if (!($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? 'Gagal memproses dokumen.');
        }

        Session::put('upload_result', $result['data']);

        return redirect()
            ->route('upload.create')
            ->with('success', 'Dokumen berhasil dianalisis. Periksa hasilnya di bawah sebelum menyimpan.');
    }

    /**
     * Tahap 2: simpan hasil pratinjau ke tabel materai.
     * api_client_id diisi NULL karena upload dilakukan langsung oleh
     * Admin melalui Dashboard, bukan melalui salah satu API client terdaftar.
     */
    public function store(Request $request)
    {
        $payload = session('upload_result');

        if (!$payload) {
            return redirect()
                ->route('upload.create')
                ->with('error', 'Tidak ada hasil pratinjau untuk disimpan. Silakan upload ulang.');
        }

        // Nomor seri dapat dikoreksi manual oleh admin sebelum disimpan
        if ($request->filled('nomor_seri')) {
            $payload['nomor_seri'] = strtoupper(trim($request->input('nomor_seri')));
        }

        $materai = $this->ocrService->store($payload, apiClientId: null);

        Session::forget('upload_result');

        return redirect()
            ->route('dashboard.show', $materai->id)
            ->with('success', 'Data materai berhasil disimpan dengan status: ' . strtoupper($materai->status));
    }

    /**
     * Batalkan pratinjau (hapus dari session tanpa menyimpan ke DB).
     */
    public function cancel()
    {
        Session::forget('upload_result');

        return redirect()
            ->route('upload.create')
            ->with('success', 'Pratinjau dibatalkan.');
    }
}
