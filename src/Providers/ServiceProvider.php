<?php
declare(strict_types=1);

namespace CountlessIntegers\Providers;

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
    }

    public function register()
    {
        $this->app->singleton(HealthCheckService::class, static function ($app) {
            return new HealthCheckService(Config::get('health-check'));
        });
    }
}
