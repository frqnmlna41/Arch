<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Kirim ulang link verifikasi email.
     *
     * Rate-limited via throttle:6,1 di route (6 kali per menit).
     * Jika email sudah terverifikasi, langsung redirect ke dashboard.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        Log::info('Kirim ulang email verifikasi.', [
            'user_id' => $request->user()->id,
            'email'   => $request->user()->email,
            'ip'      => $request->ip(),
        ]);

        return back()->with(
            'status',
            'verification-link-sent'
        );
    }
}
