<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Web\ClientController
 * ──────────────────────────────────────────────────────────────────────
 * CRUD untuk tabel api_clients melalui Dashboard Admin.
 * Mencakup juga fitur regenerasi API key, identik dengan
 * Api\ApiClientController::regenerateKey() pada REST API.
 * ──────────────────────────────────────────────────────────────────────
 */
class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiClient::latest();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $clients = $query->paginate(10)->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return view('clients.form', [
            'client' => new ApiClient(),
            'generatedKey' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['api_key'] = Str::random(64);
        $validated['is_active'] = $request->boolean('is_active', true);

        $client = ApiClient::create($validated);

        return redirect()
            ->route('clients.show-key', $client->id)
            ->with('success', 'API client "' . $client->nama . '" berhasil ditambahkan.');
    }

    public function edit(ApiClient $client)
    {
        return view('clients.form', [
            'client' => $client,
            'generatedKey' => null,
        ]);
    }

    public function update(Request $request, ApiClient $client)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $client->update($validated);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Data API client "' . $client->nama . '" berhasil diperbarui.');
    }

    public function destroy(ApiClient $client)
    {
        $nama = $client->nama;
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'API client "' . $nama . '" berhasil dihapus.');
    }

    /**
     * Regenerasi API key. Key lama otomatis tidak valid lagi
     * begitu nilainya ditimpa di basis data.
     */
    public function regenerate(ApiClient $client)
    {
        $client->update(['api_key' => Str::random(64)]);

        return redirect()
            ->route('clients.show-key', $client->id)
            ->with('success', 'API key untuk "' . $client->nama . '" berhasil diregenerasi.');
    }

    /**
     * Halaman one-time-view untuk menampilkan API key secara penuh
     * setelah dibuat/diregenerasi. Setelah ini, daftar client hanya
     * menampilkan key dalam bentuk tersamar (masked).
     */
    public function showKey(ApiClient $client)
    {
        return view('clients.show-key', [
            'client' => $client,
        ]);
    }
}
