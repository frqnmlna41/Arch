<?php

use App\Http\Controllers\Admin\AgeCategoryController;
use App\Http\Controllers\Admin\ArenaController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\DisciplineController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\PerguruanController;
use App\Http\Controllers\Admin\SportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\WinnerController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AthleteController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

/*
|──────────────────────────────────────────────────────────────────
| Tournament CMS – API Routes
| Base: /api
|
| Autentikasi menggunakan Laravel Sanctum.
| Install: composer require laravel/sanctum
| Publish: php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
|──────────────────────────────────────────────────────────────────
*/
Route::get('/', function () {
    return view('welcome');
});

// HOME
Route::get('/home', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('home');

// AUTH VIEW
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::get('/register', fn() => view('auth.register'))->name('register');

// AUTH PROCESS
Route::prefix('auth')->name('auth.')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::post('/register-perguruan', [AuthController::class, 'registerPerguruan'])
        ->name('register-perguruan');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::get('/me', [AuthController::class, 'me'])
        ->middleware('auth'); // 🔥 FIX
});

// ══════════════════════════════════════════════════════════════════
// PROTECTED ROUTES
// ══════════════════════════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // ────────────────────────────────────────────────────────────
    // ADMIN ROUTES
    // ────────────────────────────────────────────────────────────
// Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
// Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', 'role:admin'])->group(function () {

//         // Sports
//         Route::apiResource('sports', SportController::class);

//         // Disciplines
//         Route::apiResource('disciplines', DisciplineController::class);

//         // Age Categories
//         Route::apiResource('age-categories', AgeCategoryController::class);

//         // Arenas
//         Route::apiResource('arenas', ArenaController::class);

//         // Events
//         Route::apiResource('events', EventController::class);
//         Route::patch('events/{event}/status', [EventController::class, 'changeStatus'])->name('events.status');

//         // Matches
//         Route::apiResource('matches', MatchController::class);
//         Route::post('events/{event}/matches/generate',      [MatchController::class, 'generate'])->name('matches.generate');
//         Route::patch('matches/{match}/arena',               [MatchController::class, 'assignArena'])->name('matches.arena');
//         Route::patch('matches/{match}/schedule',            [MatchController::class, 'assignSchedule'])->name('matches.schedule');

//         // Winners
//         Route::get('winners',                               [WinnerController::class, 'index'])->name('winners.index');
//         Route::get('winners/{winner}',                      [WinnerController::class, 'show'])->name('winners.show');
//         Route::post('events/{event}/winners/calculate',     [WinnerController::class, 'calculate'])->name('winners.calculate');
//         Route::delete('winners/{winner}',                   [WinnerController::class, 'destroy'])->name('winners.destroy');

//         // Certificates
//         Route::get('certificates',                          [CertificateController::class, 'index'])->name('certificates.index');
//         Route::post('winners/{winner}/certificate',         [CertificateController::class, 'generate'])->name('certificates.generate');
//         Route::post('events/{event}/certificates/generate-all', [CertificateController::class, 'generateAll'])->name('certificates.generate-all');
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // SPORTS
        Route::apiResource('sports', SportController::class)
            ->middleware('permission:manage sports');

        // EVENTS
        Route::apiResource('events', EventController::class)
            ->middleware('permission:manage events');

        Route::patch('events/{event}/status',
            [EventController::class, 'changeStatus']
        )->name('events.status');

        // MATCHES
        Route::apiResource('matches', MatchController::class)
            ->middleware('permission:manage matches');

        // Disciplines
        Route::apiResource('disciplines', DisciplineController::class);

        // Age Categories
        Route::apiResource('age-categories', AgeCategoryController::class);

        // Arenas
        Route::apiResource('arenas', ArenaController::class);

        // Winners
        Route::get('winners', [WinnerController::class, 'index'])->name('winners.index');
        Route::get('winners/{winner}', [WinnerController::class, 'show'])->name('winners.show');
        Route::post('events/{event}/winners/calculate', [WinnerController::class, 'calculate'])->name('winners.calculate');
        Route::delete('winners/{winner}', [WinnerController::class, 'destroy'])->name('winners.destroy');

        // Certificates
        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::post('winners/{winner}/certificate', [CertificateController::class, 'generate'])->name('certificates.generate');
        Route::post('events/{event}/certificates/generate-all', [CertificateController::class, 'generateAll'])->name('certificates.generate-all');

        // Perguruan Management
        Route::resource('perguruan', PerguruanController::class)->parameters(['perguruans' => 'user']);
        Route::patch('perguruans/{user}/verify', [PerguruanController::class, 'verify'])->name('perguruans.verify');
        Route::patch('perguruans/{user}/reject', [PerguruanController::class, 'reject'])->name('perguruans.reject');
    });

    // ────────────────────────────────────────────────────────────
    // ATHLETE MANAGEMENT (admin + coach)
    // ────────────────────────────────────────────────────────────
    Route::apiResource('athletes', AthleteController::class);

    // ────────────────────────────────────────────────────────────
    // EVENT PARTICIPANTS (admin + coach)
    // ────────────────────────────────────────────────────────────
    Route::prefix('events/{event}')->name('events.')->group(function () {
        Route::get('participants',               [EventParticipantController::class, 'index'])->name('participants.index');
        Route::post('participants',              [EventParticipantController::class, 'store'])->name('participants.store');
        Route::delete('participants/{participant}', [EventParticipantController::class, 'destroy'])->name('participants.destroy');

        // Admin only
        Route::patch('participants/{participant}/verify', [EventParticipantController::class, 'verify'])
            ->name('participants.verify')
            ->middleware('role:admin');
        Route::patch('participants/{participant}/reject', [EventParticipantController::class, 'reject'])
            ->name('participants.reject')
            ->middleware('role:admin');
    });

    // ────────────────────────────────────────────────────────────
    // SCORING (judge + admin)
    // ────────────────────────────────────────────────────────────
    Route::prefix('matches/{match}')->name('matches.')->group(function () {
        Route::get('scores',          [ScoreController::class, 'index'])->name('scores.index');
        Route::post('scores',         [ScoreController::class, 'store'])->name('scores.store')->middleware('role:judge,admin');
        Route::put('scores/{score}',  [ScoreController::class, 'update'])->name('scores.update')->middleware('role:judge,admin');
        Route::get('scores/summary',  [ScoreController::class, 'summary'])->name('scores.summary');
    });

    // ────────────────────────────────────────────────────────────
    // SCHEDULE (semua role yang sudah login)
    // ────────────────────────────────────────────────────────────
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/',              [ScheduleController::class, 'index'])->name('index');
        Route::get('/today',         [ScheduleController::class, 'today'])->name('today');
        Route::get('/{match}',       [ScheduleController::class, 'show'])->name('show');
        Route::get('/event/{event}', [ScheduleController::class, 'byEvent'])->name('by-event');
    });

    // ────────────────────────────────────────────────────────────
    // CERTIFICATES (download – semua role)
    // ────────────────────────────────────────────────────────────
    Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])
        ->name('certificates.download');
});
