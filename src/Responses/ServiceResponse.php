<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Responses;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckResponseInterface;

class ServiceResponse implements HealthCheckResponseInterface
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

    public function getReport(): array
    {
        return $this->report;
    }

    public function addCheckerReport(string $name, HealthCheckResponseInterface $response): self
    {
        $this->is_healthy = $this->is_healthy && $response->isHealthy();
        $this->report[$name] = $response->toArray();
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
