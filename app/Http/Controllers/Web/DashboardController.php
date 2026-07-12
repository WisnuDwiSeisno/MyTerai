<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Materai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ApiClient;

/**
 * Web\DashboardController
 * ──────────────────────────────────────────────────────────────────────
 * Halaman utama dashboard: daftar data materai (dengan filter & pencarian)
 * serta halaman detail satu record materai beserta daftar duplikatnya.
 *
 * Logic query di sini sengaja dibuat senada dengan
 * MateraiController::index() & show() pada REST API, agar data yang
 * ditampilkan di UI konsisten dengan response API.
 * ──────────────────────────────────────────────────────────────────────
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua client untuk dropdown filter
        $clients = ApiClient::orderBy('nama')->get(['id', 'nama']);

        $query = Materai::with(['apiClient:id,nama', 'duplikatDari:id,nomor_seri,nama_file'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('search')) {
            $query->where('nomor_seri', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('client')) {
            if ($request->client === 'dashboard') {
                // Uploaded langsung dari dashboard admin (bukan dari API client)
                $query->whereNull('api_client_id');
            } else {
                $query->where('api_client_id', $request->client);
            }
        }

        $data = $query->paginate(10)->withQueryString();

        // Summary: kalau filter client aktif, summary ikut difilter juga
        $baseQuery = Materai::query();
        if ($request->filled('client')) {
            if ($request->client === 'dashboard') {
                $baseQuery->whereNull('api_client_id');
            } else {
                $baseQuery->where('api_client_id', $request->client);
            }
        }

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'unik' => (clone $baseQuery)->where('status', 'unik')->count(),
            'duplikat' => (clone $baseQuery)->where('status', 'duplikat')->count(),
            'tidak_terbaca' => (clone $baseQuery)->where('status', 'tidak_terbaca')->count(),
        ];

        return view('dashboard.index', [
            'materais' => $data,
            'summary' => $summary,
            'clients' => $clients,
            'filters' => $request->only(['status', 'jenis', 'search', 'client']),
        ]);
    }

    public function show($id)
    {
        $materai = Materai::with([
            'apiClient:id,nama',
            'duplikatDari:id,nomor_seri,nama_file,status,created_at',
            'duplikatList:id,nomor_seri,nama_file,status,api_client_id,created_at',
            'duplikatList.apiClient:id,nama',
        ])->findOrFail($id);

        return view('dashboard.show', [
            'materai' => $materai,
        ]);
    }

    public function viewFile($id)
    {
        $materai = Materai::findOrFail($id);

        if (!$materai->path_file) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($materai->path_file)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->file(
            storage_path('app/public/' . $materai->path_file)
        );
    }

    public function destroy($id)
    {
        $materai = Materai::findOrFail($id);

        if ($materai->duplikat_count > 0) {
            Materai::where('duplikat_dari_id', $id)
                ->update(['duplikat_dari_id' => null]);
        }

        if ($materai->path_file && Storage::disk('private')->exists($materai->path_file)) {
            Storage::disk('private')->delete($materai->path_file);
        }

        $materai->delete();

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Data materai berhasil dihapus.');
    }
}
