<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;

/**
 * Checks if the laravel log file is present and writable.
 *
 * @todo: abstract to "any file"
 */
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
        return new CheckerReport(is_writable($path));
    }
}
