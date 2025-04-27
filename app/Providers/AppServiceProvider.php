<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MockDianService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar el servicio MockDianService
        $this->app->singleton(MockDianService::class, function ($app) {
            return new MockDianService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
