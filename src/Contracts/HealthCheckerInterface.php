<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Contracts;

interface HealthCheckerInterface
{
    public function checkHealth(): HealthCheckReportInterface;
}
