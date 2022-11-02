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
    public function __construct(
        private HealthCheckService $health_check_service,
        private Repository $config,
    ) {
    }

    public function checkHealth(Request $request): JsonResponse
    {
        $access_token = $this->config->get('health-check.access_token');
        if ($access_token !== null && $access_token !== $request->query('token')) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $checks = $this->config['health-check.checkers'] ?? [];
        $extended_checks = $this->config['health-check.extended_checks'] ?? [];
        if ($request->query('extended') && $extended_checks) {
            $checks = [...$checks, ...$extended_checks];
        }

        $report = $this->health_check_service->runChecks($checks);

        if ($report->isHealthy()) {
            return new JsonResponse($report->toArray());
        }
        return new JsonResponse($report->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
