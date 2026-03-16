<?php

namespace App\Providers;

use App\Models\Athlete;
use App\Models\Event;
use App\Models\Match;
use App\Models\Score;
use App\Policies\AthletePolicy;
use App\Policies\EventPolicy;
use App\Policies\MatchPolicy;
use App\Policies\ScorePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * AuthServiceProvider
 *
 * Mendaftarkan semua Policy ke Model yang sesuai.
 *
 * CATATAN Laravel 10/11:
 * Laravel 10+ mendukung policy auto-discovery jika penamaan konvensional.
 * Contoh: App\Models\Athlete → App\Policies\AthletePolicy (otomatis terdeteksi).
 *
 * Namun lebih baik eksplisit dengan mendaftarkannya di sini
 * untuk kejelasan dan kontrol penuh.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapping Model → Policy.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Athlete::class => AthletePolicy::class,
        Match::class   => MatchPolicy::class,
        Score::class   => ScorePolicy::class,
        Event::class   => EventPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // ── Gate: Super Admin bypass ───────────────────────────────
        // Gate::before() akan dipanggil sebelum SEMUA gate & policy check.
        // Ini sebagai lapisan keamanan tambahan di luar policy individual.
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        // ── Custom Gates (untuk aksi yang tidak terikat ke model) ──

        // Gate untuk akses halaman admin
        Gate::define('access-admin-panel', function (User $user) {
            return $user->hasRole('admin');
        });

        // Gate untuk generate sertifikat (standalone, tidak terikat satu event)
        Gate::define('generate-any-certificate', function (User $user) {
            return $user->hasRole('admin');
        });

        // Gate untuk manage jadwal
        Gate::define('manage-schedule', function (User $user) {
            return $user->hasRole('admin');
        });
    }
}
