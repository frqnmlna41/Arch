<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes — SPORA Portal Olahraga
|--------------------------------------------------------------------------
|
| Grup "guest"  → hanya bisa diakses jika BELUM login
| Grup "auth"   → hanya bisa diakses jika SUDAH login
| Grup "verified" → sudah login DAN email sudah diverifikasi
|
*/

/* ==========================================================================
   GUEST — Belum login
   ========================================================================== */
Route::middleware('guest')->group(function () {

    // ── Login ──────────────────────────────────────────────────────────────
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    // ── Register ───────────────────────────────────────────────────────────
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.store');

    // ── Forgot Password ────────────────────────────────────────────────────
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // ── Reset Password ─────────────────────────────────────────────────────
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/* ==========================================================================
   AUTH — Sudah login
   ========================================================================== */
Route::middleware('auth')->group(function () {

    // ── Email Verification Prompt ──────────────────────────────────────────
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // ── Verify via Link (dari email) ───────────────────────────────────────
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // ── Resend Verification Email ──────────────────────────────────────────
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // ── Logout ─────────────────────────────────────────────────────────────
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
