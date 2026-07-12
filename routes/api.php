<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MateraiController;
use App\Http\Controllers\ApiClientController;
use App\Http\Middleware\ApiKeyCheck;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SeriCheck — API Routes
|--------------------------------------------------------------------------
|
| Semua route di sini diawali /api/ (Laravel default)
|
| Auth (Sanctum)  → untuk user dashboard (login/logout)
| ApiKeyCheck     → untuk client eksternal yang upload materai
|
*/

// ── Public: Auth ───────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',  [AuthController::class, 'login']);
});

// ── Protected: Auth (butuh Sanctum token) ─────────────────────────────
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get ('/me',     [AuthController::class, 'me']);
});

// ── Protected: Kelola API Clients (butuh Sanctum / admin) ─────────────
Route::middleware('auth:sanctum')->prefix('clients')->group(function () {
    Route::get   ('/',              [ApiClientController::class, 'index']);
    Route::post  ('/',              [ApiClientController::class, 'store']);
    Route::put   ('/{id}',         [ApiClientController::class, 'update']);
    Route::post  ('/{id}/regenerate', [ApiClientController::class, 'regenerateKey']);
    Route::delete('/{id}',         [ApiClientController::class, 'destroy']);
});

// ── Protected: Materai API (butuh X-API-Key di header) ─────────────────
Route::middleware(ApiKeyCheck::class)->prefix('materai')->group(function () {
    Route::post  ('/simpan', [MateraiController::class, 'simpan']);  // Simpan ke DB
    Route::get   ('/',        [MateraiController::class, 'index']);   // List semua
    Route::get   ('/{id}',   [MateraiController::class, 'show']);    // Detail + duplikat
    Route::post  ('/upload', [MateraiController::class, 'upload']);  // Upload → OCR (belum simpan)
    Route::delete('/{id}',   [MateraiController::class, 'destroy']); // Hapus
});
