<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Tandai email user sebagai terverifikasi.
     *
     * Request menggunakan EmailVerificationRequest bawaan Laravel
     * yang otomatis memvalidasi signature URL (signed middleware).
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()
                ->intended(route('dashboard') . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            Log::info('Email berhasil diverifikasi.', [
                'user_id' => $request->user()->id,
                'email'   => $request->user()->email,
                'ip'      => $request->ip(),
            ]);
        }

        return redirect()
            ->intended(route('dashboard') . '?verified=1');
    }
}
