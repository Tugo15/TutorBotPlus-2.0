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

        // Adaptar dinámicamente la URL base de rutas y assets a la petición actual
        // (resuelve túneles SSH localhost:8080, reverse proxies, IPs de red y dominios sin configurar .env)
        if (!app()->runningInConsole()) {
            $httpHost = request()->server->get('HTTP_HOST') ?? request()->header('host');

            // Si se accede mediante túnel SSH o localmente (localhost:8080, 127.0.0.1, etc.)
            if ($httpHost && (str_contains($httpHost, 'localhost') || str_contains($httpHost, '127.0.0.1'))) {
                URL::forceScheme('http');
                $localRoot = 'http://' . $httpHost;
                URL::forceRootUrl($localRoot);
                config(['app.asset_url' => $localRoot]);
                config(['app.mix_url' => $localRoot]);
            } else {
                $appUrl = config('app.url');
                $isHttps = (request()->server->get('HTTP_X_FORWARDED_PROTO') === 'https'
                    || request()->header('x-forwarded-proto') === 'https'
                    || request()->isSecure()
                    || str_starts_with($appUrl ?? '', 'https://'));

                if (!empty($appUrl) && !str_contains($appUrl, 'localhost')) {
                    $root = rtrim($appUrl, '/');
                    if ($isHttps) {
                        $root = preg_replace('/^http:/', 'https:', $root);
                        URL::forceScheme('https');
                    } else {
                        URL::forceScheme('http');
                    }
                    URL::forceRootUrl($root);
                    config(['app.asset_url' => $root]);
                    config(['app.mix_url' => $root]);
                } else {
                    if ($isHttps) {
                        URL::forceScheme('https');
                    }

                    $currentRoot = request()->root();
                    if (!empty($currentRoot)) {
                        if ($isHttps) {
                            $currentRoot = preg_replace('/^http:/', 'https:', $currentRoot);
                        }
                        URL::forceRootUrl($currentRoot);
                        config(['app.asset_url' => $currentRoot]);
                        config(['app.mix_url' => $currentRoot]);
                    }
                }
            }

            // Forzar al Paginator a usar el path correcto con el Root URL forzado
            Paginator::currentPathResolver(function () {
                return url()->current();
            });
        }
    }
}
