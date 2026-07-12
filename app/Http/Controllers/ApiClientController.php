<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use Illuminate\Http\Request;

class ApiClientController extends Controller
{
    // GET /api/clients — list semua client
    public function index()
    {
        $clients = ApiClient::withCount('materais')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $clients,
        ]);
    }

    // POST /api/clients — daftarkan client baru
    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Tentukan prefix key dari nama
        $prefix = 'sk_' . strtolower(preg_replace('/[^a-zA-Z]/', '', $request->nama));
        $prefix = substr($prefix, 0, 10);

        $client = ApiClient::create([
            'nama'       => $request->nama,
            'api_key'    => ApiClient::generateKey($prefix),
            'keterangan' => $request->keterangan,
            'is_active'  => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Client '{$client->nama}' berhasil didaftarkan",
            'data'    => $client,
            // Tampilkan API key sekali saja di sini
            'api_key' => $client->api_key,
        ], 201);
    }

    // PUT /api/clients/{id} — update client
    public function update(Request $request, $id)
    {
        $client = ApiClient::findOrFail($id);

        $request->validate([
            'nama'       => 'sometimes|string|max:255',
            'keterangan' => 'nullable|string',
            'is_active'  => 'sometimes|boolean',
        ]);

        $client->update($request->only(['nama', 'keterangan', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Client berhasil diperbarui',
            'data'    => $client,
        ]);
    }

    // POST /api/clients/{id}/regenerate — buat ulang API key
    public function regenerateKey($id)
    {
        $client = ApiClient::findOrFail($id);

        $prefix = 'sk_' . strtolower(preg_replace('/[^a-zA-Z]/', '', $client->nama));
        $prefix = substr($prefix, 0, 10);

        $client->update(['api_key' => ApiClient::generateKey($prefix)]);

        return response()->json([
            'success' => true,
            'message' => 'API key berhasil dibuat ulang',
            'api_key' => $client->api_key,
        ]);
    }

    // DELETE /api/clients/{id} — hapus client
    public function destroy($id)
    {
        $client = ApiClient::findOrFail($id);
        $client->delete();

        return response()->json([
            'success' => true,
            'message' => "Client '{$client->nama}' berhasil dihapus",
        ]);
    }
}
