<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan halaman lupa kata sandi.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim link reset kata sandi ke email.
     *
     * Selalu mengembalikan pesan sukses (security: tidak mengungkap
     * apakah email terdaftar atau tidak).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Link reset kata sandi dikirim.', [
                'email' => $request->email,
                'ip'    => $request->ip(),
            ]);
        } else {
            // Log untuk keperluan internal, tapi tampilan user tetap sama
            Log::notice('Permintaan reset untuk email tidak terdaftar.', [
                'email' => $request->email,
                'ip'    => $request->ip(),
            ]);
        }

        // Kembalikan pesan sukses terlepas dari hasilnya (anti-enumeration)
        return back()->with(
            'status',
            'Jika email tersebut terdaftar, kami telah mengirimkan link reset kata sandi. Periksa kotak masuk Anda.'
        );
    }
}
