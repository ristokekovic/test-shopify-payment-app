<?php

namespace Packlink\Middleware;

use Illuminate\Support\ServiceProvider;
use Packlink\Middleware\Commands\Migrate;
use Packlink\Middleware\Commands\StartMaintenanceMode;
use Packlink\Middleware\Commands\StopMaintenanceMode;
use Packlink\Middleware\Http\UrlGenerator;

class PacklinkMiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/database/migrations/' => database_path('migrations'),
            ],
            'migrations'
        );

        $this->publishes(
            [
                __DIR__ . '/resources/assets' => public_path('assets/'),
                __DIR__ . '/resources/css' => public_path('css/'),
                __DIR__ . '/resources/img' => public_path('img/'),
                __DIR__ . '/resources/js' => public_path('js/'),
            ],
            'public'
        );

        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'packlink');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'packlink');
        $this->loadRoutesFrom(__DIR__ . '/routes/packlink.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/packlink_api.php');
        $this->registerCommands();
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        include __DIR__ . '/routes/packlink.php';

        if (config('app.debug')) {
            $this->registerUrlGenerator();
        }
    }

    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerUrlGenerator(): void
    {
        // the code is copied from the \Illuminate\Routing\RoutingServiceProvider::registerUrlGenerator() method
        $this->app->singleton(
            'url',
            function ($app) {
                /** @var \Illuminate\Foundation\Application $app */
                $routes = $app['router']->getRoutes();

                $app->instance('routes', $routes);

                $url = new UrlGenerator(
                    $routes,
                    $app->rebinding(
                        'request',
                        static function ($app, $request) {
                            $app['url']->setRequest($request);
                        }
                    ),
                    $app['config']['app.asset_url']
                );

                $url->setSessionResolver(
                    function () {
                        return $this->app['session'] ?? null;
                    }
                );

                $url->setKeyResolver(
                    function () {
                        return $this->app->make('config')->get('app.key');
                    }
                );

                $app->rebinding(
                    'routes',
                    static function ($app, $routes) {
                        $app['url']->setRoutes($routes);
                    }
                );

                return $url;
            }
        );
    }

    /**
     * Registers custom artisan commands.
     */
    protected function registerCommands()
    {
        $this->commands(
            [
                StartMaintenanceMode::class,
                StopMaintenanceMode::class,
                Migrate::class,
            ]
        );
    }
}
