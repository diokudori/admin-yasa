<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
         // Should return TRUE or FALSE
        Gate::define('manage-dashboard', function(User $user) {
            return $user->role == 0;
        });

        Gate::define('manage-pbp', function(User $user) {
            return $user->role == 5;
        });

        Gate::define('manage-gudang', function(User $user) {
            return $user->role == 2;
        });

        Gate::define('manage-distribution', function(User $user) {
            return $user->role == 1;
        });
    }
}
