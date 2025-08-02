<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define Gates for menu permissions
        Gate::define('manage-routers', function ($user) {
            return $user->hasRole('super_admin');
        });

        Gate::define('manage-users', function ($user) {
            return $user->hasRole('super_admin');
        });

        Gate::define('manage-pppoe', function ($user) {
            return $user->hasRole('super_admin') || $user->hasRole('admin');
        });
    }
}
