<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Tampilkan halaman form reset kata sandi.
     *
     * Token & email dikirim via URL dari link email.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Proses reset kata sandi.
     *
     * Setelah berhasil:
     *  - Password di-hash dan disimpan
     *  - Remember token diperbarui
     *  - Event PasswordReset didispatch
     *  - Redirect ke login dengan pesan sukses
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->uncompromised(),
            ],
        ], [
            'token.required'     => 'Token reset tidak valid.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Log::info('Kata sandi berhasil direset.', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                    'ip'      => $request->ip(),
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.');
        }

        Log::warning('Reset kata sandi gagal.', [
            'status' => $status,
            'email'  => $request->email,
            'ip'     => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => match ($status) {
                Password::INVALID_TOKEN => 'Token reset tidak valid atau sudah kedaluwarsa. Minta link baru.',
                Password::INVALID_USER  => 'Email tidak terdaftar dalam sistem kami.',
                default                 => 'Terjadi kesalahan. Silakan coba lagi.',
            },
        ]);
    }
}
