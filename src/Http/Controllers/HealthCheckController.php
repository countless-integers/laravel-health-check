<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Http\Controllers\v2;

use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class HealthCheckController
{
    /**
     * @var HealthCheckService
     */
    private $health_check_service;

    public function __construct(HealthCheckService $health_check_service)
    {
        $this->health_check_service = $health_check_service;
    }

    public function checkHealth(): JsonResponse
    {
        $report = $this->health_check_service->checkServices();
        if ($report->isHealthy()) {
            return new JsonResponse($report->toArray());
        }
        return new JsonResponse($report->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
