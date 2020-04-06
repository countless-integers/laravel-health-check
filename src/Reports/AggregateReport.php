<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Reports;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;

class AggregateReport implements HealthCheckReportInterface
{
    /**
     * @var bool
     */
    private $is_healthy = true;

    /**
     * @var array
     */
    private $report = [];

    public function isHealthy(): bool
    {
        return $this->is_healthy;
    }

    public function getDetails(): array
    {
        return $this->report;
    }

    public function addCheckerReport(string $name, HealthCheckReportInterface $report): self
    {
        $this->is_healthy = $this->is_healthy && $report->isHealthy();
        $this->report[$name] = $report->toArray();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR, 512);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'is_healthy' => $this->is_healthy,
            'report' => $this->report,
        ];
    }
}
