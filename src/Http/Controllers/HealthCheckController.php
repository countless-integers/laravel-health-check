<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Http\Controllers;

use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use Illuminate\Config\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HealthCheckController
{
    /**
     * @var HealthCheckService
     */
    private $health_check_service;

    /**
     * @var Repository
     */
    private $config;

    /**
     * @param HealthCheckService $health_check_service
     * @param Repository $config
     */
    public function __construct(HealthCheckService $health_check_service, Repository $config)
    {
        $this->health_check_service = $health_check_service;
        $this->config = $config;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkHealth(Request $request): JsonResponse
    {
        $access_token = $this->config->get('health-check.access_token');
        if ($access_token !== null && $access_token !== $request->query('token')) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $report = $this->health_check_service->checkServices();
        if ($report->isHealthy()) {
            return new JsonResponse($report->toArray());
        }
        return new JsonResponse($report->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
