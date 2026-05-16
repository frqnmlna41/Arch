<?php

// routes/api.php  –  Admin route group
// Tambahkan di dalam middleware ['auth:sanctum', 'role:admin'] atau middleware admin kamu.

use App\Http\Controllers\Admin\AgeCategoryController;
use App\Http\Controllers\Admin\AthleteController;
use App\Http\Controllers\Admin\DisciplineController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PerguruanController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\SportController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {

    // ─── Sports ───────────────────────────────────────────────────────────────
    Route::apiResource('sports', SportController::class);

    // ─── Disciplines ──────────────────────────────────────────────────────────
    Route::apiResource('disciplines', DisciplineController::class);

    // ─── Age Categories ───────────────────────────────────────────────────────
    Route::apiResource('age-categories', AgeCategoryController::class);

    // ─── Events ───────────────────────────────────────────────────────────────
    Route::apiResource('events', EventController::class);

    // ─── Event Categories ─────────────────────────────────────────────────────
    Route::apiResource('event-categories', EventCategoryController::class);

    // ─── Perguruans ───────────────────────────────────────────────────────────
    Route::apiResource('perguruans', PerguruanController::class);

    // ─── Athletes ─────────────────────────────────────────────────────────────
    Route::apiResource('athletes', AthleteController::class);

    // ─── Registrations ────────────────────────────────────────────────────────
    Route::patch('registrations/{registration}/approve', [RegistrationController::class, 'approve'])
        ->name('admin.registrations.approve');

    Route::patch('registrations/{registration}/reject', [RegistrationController::class, 'reject'])
        ->name('admin.registrations.reject');

    Route::apiResource('registrations', RegistrationController::class);

    // ─── Invoices ─────────────────────────────────────────────────────────────
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])
        ->name('admin.invoices.generate');

    Route::apiResource('invoices', InvoiceController::class);
});
