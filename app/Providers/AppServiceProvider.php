<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

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
        Paginator::useBootstrapFive();

        // if (request()->server->get('HTTP_X_FORWARDED_PROTO') === 'https' || request()->header('x-forwarded-proto') === 'https') {
        //     URL::forceScheme('https');
        // }
    }
}
