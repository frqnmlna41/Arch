<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Athlete;
use App\Policies\PerguruanPolicy;
use App\Policies\AthletePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => PerguruanPolicy::class,
        Athlete::class => AthletePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates additional if needed
        Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });
    }
}

