<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DbConnectionChecker implements HealthCheckerInterface
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
        $query = $this->config['query'] ?? 'SHOW TABLES';
        try {
            $is_healthy = !empty(DB::select($query));
            return new CheckerReport($is_healthy);
        } catch (QueryException $exception) {
            return new CheckerReport(false, [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
