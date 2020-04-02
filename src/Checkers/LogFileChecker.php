<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckResponseInterface;
use CountlessIntegers\LaravelHealthCheck\Responses\DefaultResponse;
use Illuminate\Database\QueryException;

class LogFileChecker implements HealthCheckerInterface
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function checkHealth(): HealthCheckResponseInterface
    {
        $path = $this->config['log_path'] ?? '/var/log/laravel.log';
        try {
            return new DefaultResponse(is_writable($path));
        } catch (QueryException $exception) {
            return new DefaultResponse(false, [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
