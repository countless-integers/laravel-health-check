<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Http\Controllers;

use Codeception\Test\Unit;
use CountlessIntegers\LaravelHealthCheck\Http\Controllers\HealthCheckController;
use CountlessIntegers\LaravelHealthCheck\Reports\AggregateReport;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mockery;

class HealthCheckControllerTest extends Unit
{
    /**
     * @var HealthCheckService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $health_check_service_mock;

    /**
     * @var HealthCheckController
     */
    private $controller;

    protected function _before()
    {
        $this->health_check_service_mock = Mockery::mock(HealthCheckService::class);
        $this->controller = new HealthCheckController($this->health_check_service_mock);
    }

    /**
     * @test
     */
    public function itWillReturnOkIfHealthCheckPasses(): void
    {
        $report = new AggregateReport();
        $report->addCheckerReport('hello-world', new CheckerReport(true));
        $this->health_check_service_mock->expects()
            ->checkServices()
            ->andReturns($report);

        /** @var JsonResponse $response */
        $response = $this->controller->checkHealth();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(
            [
                'is_healthy' => true,
                'report' => [
                    'hello-world' => [
                        'is_healthy' => true,
                        'report' => [],
                    ],
                ],
            ],
            $response->getData(true)
        );
    }

    /**
     * @test
     */
    public function itWillReturnServerErrorResponseIfHealthCheckFails(): void
    {
        $report = new AggregateReport();
        $report->addCheckerReport('hello-world', new CheckerReport(false));
        $report->addCheckerReport('bye-world', new CheckerReport(true));
        $this->health_check_service_mock->expects()
            ->checkServices()
            ->andReturns($report);

        /** @var JsonResponse $response */
        $response = $this->controller->checkHealth();

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(
            [
                'is_healthy' => false,
                'report' => [
                    'hello-world' => [
                        'is_healthy' => false,
                        'report' => [],
                    ],
                    'bye-world' => [
                        'is_healthy' => true,
                        'report' => [],
                    ],
                ],
            ],
            $response->getData(true)
        );
    }
}
