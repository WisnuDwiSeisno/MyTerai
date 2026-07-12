<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UploadController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});


Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Redirect "/" ke dashboard
    Route::get('/', fn() => redirect()->route('dashboard.index'));

    // Dashboard: list & detail data materai
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/{id}', [DashboardController::class, 'show'])->name('dashboard.show');
    Route::delete('/dashboard/{id}', [DashboardController::class, 'destroy'])->name('dashboard.destroy');
    Route::get('/materai/{id}/file', [DashboardController::class, 'viewFile'])
        ->name('dashboard.file');

    // Uji Coba Deteksi (Upload OCR dari UI)
    Route::get('/upload', [UploadController::class, 'create'])->name('upload.create');
    Route::post('/upload/preview', [UploadController::class, 'preview'])->name('upload.preview');
    Route::post('/upload/store', [UploadController::class, 'store'])->name('upload.store');
    Route::post('/upload/cancel', [UploadController::class, 'cancel'])->name('upload.cancel');

    // Kelola API Client
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('/clients/{client}/regenerate', [ClientController::class, 'regenerate'])->name('clients.regenerate');
    Route::get('/clients/{client}/key', [ClientController::class, 'showKey'])->name('clients.show-key');
});