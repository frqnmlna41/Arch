<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AthleteController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\Admin\AgeCategoryController;
use App\Http\Controllers\Admin\ArenaController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\DisciplineController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\SportController;
use App\Http\Controllers\Admin\WinnerController;

/*
* API Routes v1 - Authenticated via Sanctum
*/
Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth:sanctum'])->prefix('api/v1')->name('api.v1.')->group(function () {

    // ADMIN Routes (role:admin)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::apiResource('sports', SportController::class);
        Route::apiResource('disciplines', DisciplineController::class);
        Route::apiResource('age-categories', AgeCategoryController::class);
        Route::apiResource('events', EventController::class);
        Route::apiResource('arenas', ArenaController::class);
        Route::apiResource('matches', MatchController::class);
        Route::apiResource('winners', WinnerController::class);
        Route::apiResource('certificates', CertificateController::class);
    });

    // Multi-role: Athletes
    Route::apiResource('athletes', AthleteController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    // Events Participants: /events/{event}/participants
    Route::prefix('events')->name('events.')->group(function () {
        Route::apiResource('participants', EventParticipantController::class)->shallow()->only(['index', 'store']);
        Route::prefix('participants')->name('participants.')->group(function () {
            Route::post('{participant}/verify', [EventParticipantController::class, 'verify']);
            Route::post('{participant}/reject', [EventParticipantController::class, 'reject']);
        });
    });

    // Scores: /matches/{match}/scores
    Route::prefix('matches')->name('matches.')->group(function () {
        Route::get('{match}/scores', [ScoreController::class, 'index']);
        Route::post('{match}/scores', [ScoreController::class, 'store']);
        Route::put('{match}/scores/{score}', [ScoreController::class, 'update']);
    });

    // Schedules: all roles
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']);
        Route::get('/today', [ScheduleController::class, 'today']);
        Route::get('/{match}', [ScheduleController::class, 'show']);
        Route::get('/events/{event}', [ScheduleController::class, 'byEvent']);
    });

});
