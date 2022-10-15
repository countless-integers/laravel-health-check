<?php

declare(strict_types=1);

namespace Tests;

use Aws\Laravel\AwsServiceProvider;
use CountlessIntegers\LaravelHealthCheck\Http\Controllers\HealthCheckController;
use CountlessIntegers\LaravelHealthCheck\Providers\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase;

class AppTestCase extends TestCase
{
    /** @param Application $app */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            AwsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'AWS' => \Aws\Laravel\AwsFacade::class,
        ];
    }

    /** @param Router $router */
    protected function defineRoutes($router)
    {
        $router->get('/health-check', [HealthCheckController::class, 'checkHealth'])->name('health-check');
    }

    /** @param Application $app */
    protected function defineEnvironment($app)
    {

        $app['config']->set('health-check', require('config/health-check.php'));
    }
}
