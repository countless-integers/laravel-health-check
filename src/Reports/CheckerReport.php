<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Reports;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;

class CheckerReport implements HealthCheckReportInterface
{
    /**
     * @var bool
     */
    private $is_healthy;

    /**
     * @var array
     */
    private $details;

    public function __construct(bool $is_healthy, array $details = [])
    {
        $this->is_healthy = $is_healthy;
        $this->details = $details;
    }

    public function isHealthy(): bool
    {
        return $this->is_healthy;
    }

    public function getDetails(): array
    {
        return $this->details;
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
            'report' => $this->details,
        ];
    }
}
