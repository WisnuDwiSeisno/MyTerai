<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Web\AuthController
 * ──────────────────────────────────────────────────────────────────────
 * Login/logout untuk Dashboard Admin (Blade UI).
 *
 * Catatan: ini TERPISAH dari Api\AuthController yang menghasilkan token
 * Sanctum untuk konsumen API eksternal. Dashboard menggunakan session
 * auth bawaan Laravel (guard "web") karena UI-nya server-rendered.
 *
 * Model yang dipakai tetap App\Models\User (tabel users) yang sama,
 * sehingga 1 akun admin bisa login baik lewat API maupun Dashboard.
 * ──────────────────────────────────────────────────────────────────────
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard.index'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }
}
