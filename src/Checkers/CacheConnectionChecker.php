<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use Carbon\Carbon;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckResponseInterface;
use CountlessIntegers\LaravelHealthCheck\Responses\DefaultResponse;
use Illuminate\Support\Facades\Cache;

class CacheConnectionChecker implements HealthCheckerInterface
{
    public function checkHealth(): HealthCheckResponseInterface
    {
        $prefix = 'health-check-key-';
        $key = uniqid($prefix, true);
        $value = uniqid($prefix, true);
        Cache::put($key, $value, Carbon::now()->addSeconds(5));
        return new DefaultResponse(Cache::get($key) === $value);
    }
}
