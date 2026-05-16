<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\RegistrationController as UserRegistrationController;
use App\Http\Controllers\User\AthleteController as UserAthleteController;
use App\Http\Controllers\Admin\SportController;
use App\Http\Controllers\Admin\DisciplineController;
use App\Http\Controllers\Admin\AgeCategoryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\PerguruanController;
use App\Http\Controllers\Admin\AthleteController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;





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
Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        Route::apiResource('sports', SportController::class);
        Route::apiResource('disciplines', DisciplineController::class);
        Route::apiResource('age-categories', AgeCategoryController::class);
        Route::apiResource('events', EventController::class);
        Route::apiResource('event-categories', EventCategoryController::class);
        Route::apiResource('perguruans', PerguruanController::class);
        Route::apiResource('athletes', AthleteController::class);
        Route::apiResource('registrations', RegistrationController::class);
        Route::apiResource('invoices', InvoiceController::class);

    });
Route::prefix('admin/registrations')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::patch('{id}/approve', [RegistrationController::class, 'approve']);
    Route::patch('{id}/reject', [RegistrationController::class, 'reject']);
});
Route::prefix('admin/invoices')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('generate', [InvoiceController::class, 'generate']);
});
Route::get('admin/registrations', [RegistrationController::class, 'index']);

// Route::middleware(['auth:sanctum'])
//     ->prefix('user')
//     ->group(function () {

//         Route::apiResource('athletes', UserAthleteController::class)->only([
//             'index', 'store', 'update', 'destroy'
//         ]);

//         Route::apiResource('registrations', UserRegistrationController::class)->only([
//             'index', 'store', 'show'
//         ]);

//     });
