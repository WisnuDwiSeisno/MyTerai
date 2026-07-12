<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiKeyCheck
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        // ── LAPIS 1: Cek Sanctum Token ──────────────────────────
        // Ambil token dari header Authorization: Bearer xxx
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token login wajib disertakan. Login dulu via POST /api/auth/login',
            ], 401);
        }

        // Cari token di tabel personal_access_tokens
        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak unik atau sudah expired',
            ], 401);
        }

        // Cek apakah token sudah expired (opsional, sesuai config sanctum.expiration)
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            $accessToken->delete(); // hapus token expired
            return response()->json([
                'success' => false,
                'message' => 'Token sudah expired. Silakan login ulang',
            ], 401);
        }

        // Update last_used_at token
        $accessToken->forceFill(['last_used_at' => now()])->save();

        // Inject user ke request agar Auth::user() bisa dipakai
        $request->setUserResolver(fn() => $accessToken->tokenable);

        // ── LAPIS 2: Cek API Key ─────────────────────────────────
        $key = $request->header('X-API-Key');

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'API key wajib disertakan di header X-API-Key',
            ], 401);
        }

        $client = ApiClient::where('api_key', $key)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak dikenali',
            ], 401);
        }

        if (!$client->is_active) {
            return response()->json([
                'success' => false,
                'message' => "API key untuk '{$client->nama}' sedang dinonaktifkan",
            ], 403);
        }

        // ── LAPIS 3: Pastikan token milik user yang punya api_client ini ──
        // Mencegah orang pakai token milik user A tapi api_key milik user B
        $user = $accessToken->tokenable;

        if ($client->user_id && $client->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Token dan API key tidak cocok',
            ], 403);
        }

        // ── Semua unik → lanjut ────────────────────────────────
        $client->update(['last_used_at' => now()]);

        $request->attributes->set('api_client', $client);
        $request->attributes->set('auth_user', $user);

        $response = $next($request);

        $this->log($request, $client, $response, $start);

        return $response;
    }

    private function log(Request $request, ApiClient $client, $response, float $start): void
    {
        try {
            ApiLog::create([
                'api_client_id' => $client->id,
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'request_body' => json_encode($request->except(['file', 'password'])),
                'response_body' => substr($response->getContent(), 0, 2000),
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai log gagal merusak response
        }
    }
}