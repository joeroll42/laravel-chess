<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Inertia::share([
            'auth.user' => fn() => auth()->user(),
        ]);

        // ✅ Register custom migration path
        $this->loadMigrationsFrom(database_path('migrations/Init'));
        $this->loadMigrationsFrom(database_path('migrations/LoadedMigrations'));
        $this->loadMigrationsFrom(database_path('migrations/UpdateMigrations'));

    }
}
