<?php

namespace App\Providers;

use App\Traits\PaymentService;
use App\Traits\TheTellerPaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
     }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }

    public $singletons = [
        PaymentService::class => TheTellerPaymentService::class
    ];
}
