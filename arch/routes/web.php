<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// ADMIN
use App\Http\Controllers\Admin\{
    DashboardController,
    SportController,
    EventController,
    MatchController,
    DisciplineController,
    AgeCategoryController,
    ArenaController,
    AthleteController,
    WinnerController,
    CertificateController,
    PerguruanController,
    AccountController,
    InvoiceController,
    ContestController,
    SessionController,
};

// GLOBAL
use App\Http\Controllers\{
    EventParticipantController,
    ScoreController,
    ScheduleController
};

// COACH
use App\Http\Controllers\Coach\{
    DashboardController as CoachDashboardController,
    AthleteController as CoachAthleteController,
    CoachInvoiceController,
    CoachRegistrationController
};

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
// Route::view('/', 'welcome');
Route::get('/', function () {
    return view('welcome');
});
// Route::get('/home', fn() => redirect()->route('admin.dashboard'))
//     ->middleware('auth')
//     ->name('home');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->name('auth.')->group(function () {

    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/register-perguruan', 'auth.register-perguruan')->name('register-perguruan');

    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register-perguruan', [AuthController::class, 'registerPerguruan']);

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::get('/me', [AuthController::class, 'me'])
        ->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // MASTER DATA
        Route::apiResource('sports', SportController::class)->middleware('permission:manage sports');
        Route::apiResource('events', EventController::class)->middleware('permission:manage events');
        Route::resource('disciplines', DisciplineController::class);
        Route::resource('age-categories', AgeCategoryController::class);
        Route::resource('arenas', ArenaController::class);
        // Route::apiResource('arenas', ArenaController::class);

        // EVENTS EXTRA
        Route::patch('events/{event}/status', [EventController::class, 'changeStatus'])->name('events.status');

        // MATCHES
        Route::resource('matches', MatchController::class);
        Route::prefix('matches/{match}')->group(function () {
            Route::patch('schedule', [MatchController::class, 'assignSchedule'])->name('matches.schedule');
            Route::patch('arena', [MatchController::class, 'assignArena'])->name('matches.arena');
        });
        Route::post('matches/generate', [MatchController::class, 'generate'])->name('matches.generate');

        // ATHLETES
        Route::resource('athletes', AthleteController::class);

        // WINNERS
        Route::prefix('winners')->name('winners.')->group(function () {
            Route::get('/', [WinnerController::class, 'index'])->name('index');
            Route::get('{winner}', [WinnerController::class, 'show'])->name('show');
            Route::delete('{winner}', [WinnerController::class, 'destroy'])->name('destroy');
        });

        Route::post('events/{event}/winners/calculate', [WinnerController::class, 'calculate'])
            ->name('winners.calculate');

        // CERTIFICATES
        Route::prefix('certificates')->name('certificates.')->group(function () {
            Route::get('/', [CertificateController::class, 'index'])->name('index');
            Route::post('winners/{winner}', [CertificateController::class, 'generate'])->name('generate');
            Route::post('events/{event}/generate-all', [CertificateController::class, 'generateAll'])->name('generate-all');
        });

        // PERGURUAN
        Route::resource('perguruans', PerguruanController::class)->parameters(['perguruans' => 'user']);
        Route::patch('perguruans/{user}/verify', [PerguruanController::class, 'verify'])->name('perguruans.verify');
        Route::patch('perguruans/{user}/reject', [PerguruanController::class, 'reject'])->name('perguruans.reject');

        // COACH MANAGEMENT
        Route::resource('coaches', AccountController::class)->parameters(['coaches' => 'user']);
        Route::patch('coaches/{user}/verify', [AccountController::class, 'verify'])->name('coaches.verify');
        Route::patch('coaches/{user}/reject', [AccountController::class, 'reject'])->name('coaches.reject');

        // INVOICES ✅ FIX (no double prefix)
        Route::resource('invoices', InvoiceController::class);
        Route::prefix('invoices/{invoice}')->name('invoices.')->group(function () {
            Route::post('send', [InvoiceController::class, 'send'])->name('send');
            Route::post('pay', [InvoiceController::class, 'markPaid'])->name('pay');
            Route::post('cancel', [InvoiceController::class, 'cancel'])->name('cancel');

            Route::post('items', [InvoiceController::class, 'addItem'])->name('items.add');
            Route::delete('items/{item}', [InvoiceController::class, 'removeItem'])->name('items.remove');
        });
        // CONTESTS
        Route::resource('contests', ContestController::class);
        Route::get('contests', [ContestController::class, 'index'])->name('contests.index');
        Route::get('contests/{eventCategory}', [ContestController::class, 'show'])->name('contests.show');
        Route::post('contests/{eventCategory}/generate', [ContestController::class, 'generate'])->name('contests.generate');
        Route::get('contests/{eventCategory}/schedule', [ContestController::class, 'editSchedule'])->name('contests.schedule');
        Route::put('contests/{eventCategory}/schedule', [ContestController::class, 'updateSchedule'])->name('contests.schedule.update');
    
        // SESION
        Route::get('sessions',                  [SessionController::class, 'index'])->name('sessions.index');
        Route::get('sessions/create',           [SessionController::class, 'create'])->name('sessions.create');
        Route::post('sessions',                 [SessionController::class, 'store'])->name('sessions.store');
        Route::get('sessions/{session}',        [SessionController::class, 'show'])->name('sessions.show');
        Route::get('sessions/{session}/edit',   [SessionController::class, 'edit'])->name('sessions.edit');
        Route::put('sessions/{session}',        [SessionController::class, 'update'])->name('sessions.update');
        Route::put('sessions/{session}/order',  [SessionController::class, 'updateOrder'])->name('sessions.order');
    
        // SEEION GENERATED
        Route::post('sessions/generate-all', [SessionController::class, 'generateAll'])
        ->name('sessions.generate-all');
    });

/*
|--------------------------------------------------------------------------
| SHARED (ADMIN / COACH / JUDGE)
|--------------------------------------------------------------------------
*/

// EVENT PARTICIPANTS
Route::prefix('events/{event}')->name('events.')->group(function () {
    Route::get('participants', [EventParticipantController::class, 'index'])->name('participants.index');
    Route::post('participants', [EventParticipantController::class, 'store'])->name('participants.store');
    Route::delete('participants/{participant}', [EventParticipantController::class, 'destroy'])->name('participants.destroy');

    Route::patch('participants/{participant}/verify', [EventParticipantController::class, 'verify'])
        ->middleware('role:admin')->name('participants.verify');

    Route::patch('participants/{participant}/reject', [EventParticipantController::class, 'reject'])
        ->middleware('role:admin')->name('participants.reject');
});

// SCORING
Route::prefix('matches/{match}')->name('matches.')->group(function () {
    Route::get('scores', [ScoreController::class, 'index'])->name('scores.index');
    Route::post('scores', [ScoreController::class, 'store'])->middleware('role:judge,admin')->name('scores.store');
    Route::put('scores/{score}', [ScoreController::class, 'update'])->middleware('role:judge,admin')->name('scores.update');
    Route::get('scores/summary', [ScoreController::class, 'summary'])->name('scores.summary');
});

// SCHEDULE
Route::prefix('schedule')->name('schedule.')->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('index');
    Route::get('/today', [ScheduleController::class, 'today'])->name('today');
    Route::get('/{match}', [ScheduleController::class, 'show'])->name('show');
    Route::get('/event/{event}', [ScheduleController::class, 'byEvent'])->name('by-event');
});

// CERTIFICATE DOWNLOAD
Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])
    ->name('certificates.download');

/*
|--------------------------------------------------------------------------
| COACH
|--------------------------------------------------------------------------
*/
Route::prefix('coach')
    ->name('coach.')
    ->middleware(['auth:sanctum', 'role:coach'])
    ->group(function () {

        Route::get('/dashboard', [CoachDashboardController::class, 'index'])->name('dashboard');

        Route::resource('athletes', CoachAthleteController::class);
        Route::resource('invoices', CoachInvoiceController::class);
        // Route::resource('registrations', CoachRegistrationController::class);
    });


// // Operator — input nilai
Route::prefix('operator')->name('operator.')->middleware(['auth', 'role:operator'])->group(function () {
    Route::get('scores', [TaoluScoreController::class, 'index'])->name('scores.index');
    Route::get('scores/{contest}/edit', [TaoluScoreController::class, 'edit'])->name('scores.edit');
    Route::put('scores/{contest}', [TaoluScoreController::class, 'update'])->name('scores.update');
});