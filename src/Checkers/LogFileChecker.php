<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
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

    public function checkHealth(): HealthCheckReportInterface
    {
        $path = $this->config['log_path'] ?? '/var/log/laravel.log';
        try {
            return new CheckerReport(is_writable($path));
        } catch (QueryException $exception) {
            return new CheckerReport(false, [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
