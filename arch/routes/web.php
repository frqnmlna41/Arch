<?php

use App\Http\Controllers\Admin\ArenaController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\MatchController as AdminMatchController;
use App\Http\Controllers\Admin\SportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WinnerController;
use App\Http\Controllers\Athlete\ScheduleController as AthleteSchedule;
use App\Http\Controllers\Athlete\ResultController as AthleteResult;
use App\Http\Controllers\Coach\AthleteController;
use App\Http\Controllers\Coach\ParticipantController;
use App\Http\Controllers\Coach\ScheduleController as CoachSchedule;
use App\Http\Controllers\Judge\MatchController as JudgeMatchController;
use App\Http\Controllers\Judge\ScoringController;
use Illuminate\Support\Facades\Route;

/*
|──────────────────────────────────────────────────────────────────
| Tournament CMS – Route Definitions
|──────────────────────────────────────────────────────────────────
|
| Semua route dilindungi middleware:
|   'auth'       → harus login
|   'verified'   → email sudah diverifikasi (opsional)
|   'role:X'     → dari Spatie (alias di Kernel.php)
|   'permission:X' → dari Spatie
|
| Middleware Spatie harus didaftarkan di app/Http/Kernel.php:
|   'role'       => \Spatie\Permission\Middlewares\RoleMiddleware::class,
|   'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
|   'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
|
*/

// ══════════════════════════════════════════════════════════════════
// PUBLIC ROUTES (tidak perlu login)
// ══════════════════════════════════════════════════════════════════
Route::get('/', fn () => view('welcome'))->name('home');

// Breeze / Jetstream / Fortify Authentication Routes
// require __DIR__.'/auth.php';

// ══════════════════════════════════════════════════════════════════
// AUTHENTICATED ROUTES (harus login)
// ══════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->group(function () {

    // ──────────────────────────────────────────────────────────────
    // ADMIN ROUTES
    // Middleware: role:admin
    // Akses: full control terhadap semua resource
    // ──────────────────────────────────────────────────────────────
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:admin'])
        ->group(function () {

            // Dashboard
            Route::get('/dashboard', [AdminDashboard::class, 'index'])
                ->name('dashboard');

            // User Management
            Route::resource('users', UserController::class)
                ->names('users');

            // Sport & Discipline
            Route::resource('sports', SportController::class)
                ->names('sports');

            Route::resource('sports.disciplines', \App\Http\Controllers\Admin\DisciplineController::class)
                ->shallow()
                ->names('disciplines');

            // Arena
            Route::resource('arenas', ArenaController::class)
                ->names('arenas');

            // Events
            Route::resource('events', EventController::class)
                ->names('events');

            // Matches (Jadwal Pertandingan)
            Route::resource('matches', AdminMatchController::class)
                ->names('matches');

            // Winners
            Route::resource('winners', WinnerController::class)
                ->names('winners');

            // Certificates
            Route::prefix('certificates')->name('certificates.')->group(function () {
                Route::get('/', [CertificateController::class, 'index'])
                    ->name('index');
                Route::post('/generate/{winner}', [CertificateController::class, 'generate'])
                    ->name('generate')
                    ->middleware('permission:generate certificates');
                Route::get('/download/{certificate}', [CertificateController::class, 'download'])
                    ->name('download');
            });

            // Participants (verifikasi pendaftaran)
            Route::prefix('participants')->name('participants.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ParticipantController::class, 'index'])
                    ->name('index');
                Route::patch('/{participant}/verify', [\App\Http\Controllers\Admin\ParticipantController::class, 'verify'])
                    ->name('verify');
                Route::patch('/{participant}/reject', [\App\Http\Controllers\Admin\ParticipantController::class, 'reject'])
                    ->name('reject');
            });
        });

    // ──────────────────────────────────────────────────────────────
    // COACH ROUTES
    // Middleware: role:coach
    // Akses: manage own athletes, register to event, view schedule
    // ──────────────────────────────────────────────────────────────
    Route::prefix('coach')
        ->name('coach.')
        ->middleware(['role:coach'])
        ->group(function () {

            // Dashboard coach
            Route::get('/dashboard', fn () => view('coach.dashboard'))
                ->name('dashboard');

            // Athlete management (coach hanya bisa kelola atlet miliknya)
            // Authorization detail ada di AthleteController menggunakan $this->authorize()
            Route::resource('athletes', AthleteController::class)
                ->except(['delete'])  // coach tidak bisa hapus
                ->names('athletes')
                ->middleware('permission:create athletes|update athletes|view athletes');

            // Register atlet ke event
            Route::prefix('participants')->name('participants.')->group(function () {
                Route::get('/', [ParticipantController::class, 'index'])
                    ->name('index')
                    ->middleware('permission:register participant');
                Route::get('/create', [ParticipantController::class, 'create'])
                    ->name('create')
                    ->middleware('permission:register participant');
                Route::post('/', [ParticipantController::class, 'store'])
                    ->name('store')
                    ->middleware('permission:register participant');
                Route::delete('/{participant}', [ParticipantController::class, 'destroy'])
                    ->name('destroy'); // tarik pendaftaran
            });

            // Jadwal pertandingan atlet milik coach
            Route::get('/schedule', [CoachSchedule::class, 'index'])
                ->name('schedule')
                ->middleware('permission:view schedule');

            Route::get('/matches/{match}', [CoachSchedule::class, 'show'])
                ->name('matches.show')
                ->middleware('permission:view matches');
        });

    // ──────────────────────────────────────────────────────────────
    // JUDGE ROUTES
    // Middleware: role:judge
    // Akses: view schedule + input/update score
    // ──────────────────────────────────────────────────────────────
    Route::prefix('judge')
        ->name('judge.')
        ->middleware(['role:judge'])
        ->group(function () {

            // Dashboard
            Route::get('/dashboard', fn () => view('judge.dashboard'))
                ->name('dashboard');

            // Jadwal (view only)
            Route::get('/matches', [JudgeMatchController::class, 'index'])
                ->name('matches.index')
                ->middleware('permission:view schedule');

            Route::get('/matches/{match}', [JudgeMatchController::class, 'show'])
                ->name('matches.show')
                ->middleware('permission:view matches');

            // Scoring
            Route::prefix('scoring')->name('scoring.')->group(function () {

                Route::get('/{match}', [ScoringController::class, 'index'])
                    ->name('index')
                    ->middleware('permission:input score');

                Route::post('/{match}', [ScoringController::class, 'store'])
                    ->name('store')
                    ->middleware('permission:input score');

                Route::put('/{match}/scores/{score}', [ScoringController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:update score');
            });
        });

    // ──────────────────────────────────────────────────────────────
    // ATHLETE ROUTES
    // Middleware: role:athlete
    // Akses: view schedule + view own results
    // ──────────────────────────────────────────────────────────────
    Route::prefix('athlete')
        ->name('athlete.')
        ->middleware(['role:athlete'])
        ->group(function () {

            // Dashboard
            Route::get('/dashboard', fn () => view('athlete.dashboard'))
                ->name('dashboard');

            // Jadwal pertandingan
            Route::get('/schedule', [AthleteSchedule::class, 'index'])
                ->name('schedule')
                ->middleware('permission:view schedule');

            Route::get('/schedule/{match}', [AthleteSchedule::class, 'show'])
                ->name('schedule.show')
                ->middleware('permission:view matches');

            // Hasil pertandingan dan sertifikat (read-only)
            Route::get('/results', [AthleteResult::class, 'index'])
                ->name('results')
                ->middleware('permission:view results');

            Route::get('/results/{match}', [AthleteResult::class, 'show'])
                ->name('results.show')
                ->middleware('permission:view results');

            Route::get('/certificates', [AthleteResult::class, 'certificates'])
                ->name('certificates')
                ->middleware('permission:view certificates');
        });

    // ──────────────────────────────────────────────────────────────
    // SHARED ROUTES (multi-role access)
    // Menggunakan role_or_permission middleware
    // ──────────────────────────────────────────────────────────────
    Route::prefix('shared')
        ->name('shared.')
        ->middleware(['role_or_permission:view schedule'])
        ->group(function () {
            // Halaman jadwal umum yang bisa diakses semua role
            Route::get('/schedule', fn () => view('shared.schedule'))
                ->name('schedule');
        });
});
