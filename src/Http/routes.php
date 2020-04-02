<?php
declare(strict_types=1);

use CountlessIntegers\LaravelHealthCheck\Http\Controllers\v2\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get(
    'health-check',
    [
        'as' => 'health-check',
        'uses' => HealthCheckController::class . '@checkHealth',
    ]
);
