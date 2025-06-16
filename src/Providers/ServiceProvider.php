<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Providers;

use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use Illuminate\Support\AggregateServiceProvider;
use Illuminate\Support\Facades\Config;

class ServiceProvider extends AggregateServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/health-check.php' => config_path('health-check.php'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');
    }

    #[\Override]
    public function register()
    {
        $this->app->singleton(HealthCheckService::class, static function () {
            return new HealthCheckService(Config::get('health-check'));
        });
    }
}
