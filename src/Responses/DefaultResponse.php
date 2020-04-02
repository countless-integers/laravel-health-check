<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Responses;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckResponseInterface;

class DefaultResponse implements HealthCheckResponseInterface
{
    /**
     * @var bool
     */
    private $is_healthy;

    /**
     * @var array
     */
    private $report;

    public function __construct(bool $is_healthy, array $report = [])
    {
        $this->is_healthy = $is_healthy;
        $this->report = $report;
    }

    public function isHealthy(): bool
    {
        return $this->is_healthy;
    }

    public function getReport(): array
    {
        return $this->report;
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
