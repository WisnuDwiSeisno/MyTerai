<?php

namespace App\Http\Controllers;

use App\Http\services\MateraiOcrService;
use App\Models\Materai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MateraiController extends Controller
{
    private MateraiOcrService $ocrService;

    public function __construct(MateraiOcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    // ────────────────────────────────────────────────────
    // GET /api/materai
    // ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Materai::with(['apiClient:id,nama', 'duplikatDari:id,nomor_seri,nama_file'])
            ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->jenis) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->search) {
            $query->where('nomor_seri', 'like', '%' . $request->search . '%');
        }

        $data = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
            'summary' => [
                'total' => Materai::count(),
                'unik' => Materai::where('status', 'unik')->count(),
                'duplikat' => Materai::where('status', 'duplikat')->count(),
                'tidak_terbaca' => Materai::where('status', 'tidak_terbaca')->count(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────
    // GET /api/materai/{id}
    // ────────────────────────────────────────────────────
    public function show($id)
    {
        $materai = Materai::with([
            'apiClient:id,nama',
            'duplikatDari:id,nomor_seri,nama_file,status,created_at',
            'duplikatList:id,nomor_seri,nama_file,status,api_client_id,created_at',
            'duplikatList.apiClient:id,nama',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $materai,
        ]);
    }

    // ────────────────────────────────────────────────────
    // POST /api/materai/upload
    // Pemrosesan dipindah ke MateraiOcrService — sama persis
    // dengan yang dipakai dashboard, supaya tidak ada duplikasi logic.
    // ────────────────────────────────────────────────────
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $client = $request->attributes->get('api_client');

        $result = $this->ocrService->analyze($request->file('file'));

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        // Tambahkan info uploader — khusus dibutuhkan oleh response API
        $result['data']['uploaded_by'] = $client->nama;

        return response()->json([
            'success' => true,
            'data' => $result['data'],
        ]);
    }

    // ────────────────────────────────────────────────────
    // POST /api/materai/simpan
    // Pemrosesan dipindah ke MateraiOcrService::store() —
    // logic duplikasi sama persis dengan dashboard.
    // ────────────────────────────────────────────────────
    public function simpan(Request $request)
    {
        $request->validate([
            'nomor_seri' => 'nullable|string|max:100',
            'status' => 'required|in:unik,duplikat,tidak_terbaca',
            'nama_file' => 'required|string',
            'path_file' => 'required|string',
            'jenis' => 'nullable|string',
            'confidence' => 'nullable|integer',
            'raw_text' => 'nullable|string',
            'qr_image_url' => 'nullable|string',
        ]);

        $client = $request->attributes->get('api_client');

        $materai = $this->ocrService->store($request->all(), $client->id);

        $materai->load(['apiClient:id,nama', 'duplikatDari:id,nomor_seri,nama_file,duplikat_count']);

        $responseData = [
            'success' => true,
            'message' => 'Data materai berhasil disimpan',
            'data' => $materai,
        ];

        if ($materai->duplikat_dari_id) {
            $asli = Materai::with('apiClient:id,nama')->find($materai->duplikat_dari_id);
            $responseData['duplikat_dari'] = [
                'id' => $asli->id,
                'nomor_seri' => $asli->nomor_seri,
                'nama_file' => $asli->nama_file,
                'uploaded_by' => $asli->apiClient?->nama,
                'tanggal' => $asli->created_at?->format('d M Y H:i'),
                'duplikat_count' => $asli->duplikat_count,
            ];
        }

        return response()->json($responseData, 201);
    }

    // ────────────────────────────────────────────────────
    // DELETE /api/materai/{id}
    // ────────────────────────────────────────────────────
    public function destroy($id)
    {
        $materai = Materai::findOrFail($id);

        if ($materai->duplikat_count > 0) {
            Materai::where('duplikat_dari_id', $id)
                ->update(['duplikat_dari_id' => null]);
        }

        if ($materai->path_file && Storage::disk('public')->exists($materai->path_file)) {
            Storage::disk('public')->delete($materai->path_file);
        }

        $materai->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data materai berhasil dihapus',
        ]);
    }
}