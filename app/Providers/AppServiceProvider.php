<?php

namespace App\Providers;

use App\Services\GlobalPaymentService;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, GlobalPaymentService::class);
     }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
