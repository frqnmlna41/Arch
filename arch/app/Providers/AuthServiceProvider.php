<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Athlete;
use App\Policies\UserPolicy;
use App\Policies\PerguruanPolicy;
use App\Policies\AthletePolicy;
USE App\Models\Registration;
use App\Models\Invoice;
use App\Policies\RegistrationPolicy;
use App\Policies\InvoicePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Athlete::class => AthletePolicy::class,
        Registration::class => RegistrationPolicy::class,
        Invoice::class      => InvoicePolicy::class,
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

