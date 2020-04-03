<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Services;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use CountlessIntegers\LaravelHealthCheck\Reports\AggregateReport;
use Illuminate\Support\Facades\App;
use Throwable;

class HealthCheckService
{
    /**
     * @var array
     */
    private $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function checkServices(): HealthCheckReportInterface
    {
        $report = new AggregateReport();
        foreach ($this->config['checkers'] as $key => $value) {
            $checker_class = $value;
            $checker_config = [];
            if (is_array($value)) {
                $checker_class = $key;
                $checker_config = $value;
            }
            try {
                $checker = App::make($checker_class, ['config' => $checker_config]);
                $report->addCheckerReport($checker_class, $checker->checkHealth());
            } catch (Throwable $exception) {
                $report->addCheckerReport($checker_class, new CheckerReport(false, [
                    'exception' => get_class($exception),
                    'exception_message' => $exception->getMessage(),
                ]));
            }
        }
        return $report;
    }
}
