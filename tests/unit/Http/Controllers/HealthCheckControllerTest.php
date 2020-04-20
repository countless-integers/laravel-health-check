<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Http\Controllers;

use Codeception\Test\Unit;
use CountlessIntegers\LaravelHealthCheck\Http\Controllers\HealthCheckController;
use CountlessIntegers\LaravelHealthCheck\Reports\AggregateReport;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use Illuminate\Config\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    /**
     * @var Repository
     */
    private $config;

    /**
     * @var Request|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $request;

    protected function _before(): void
    {
        $this->health_check_service_mock = Mockery::mock(HealthCheckService::class);
        $this->config = new Repository();
        $this->request = Mockery::mock(Request::class);
        $this->controller = new HealthCheckController($this->health_check_service_mock, $this->config);
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

        $response = $this->controller->checkHealth($this->request);

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

        $response = $this->controller->checkHealth($this->request);

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

    /**
     * @test
     */
    public function itWillReturn404IfAccessTokenSpecifiedButNotPresent(): void
    {
        $this->config->set('health-check.access_token', 'whatever-value');
        $this->request->expects()->query('token');

        $response = $this->controller->checkHealth($this->request);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function itWillReturnResponseIfAccessTokenSpecifiedAndPresent(): void
    {
        $token = 'whatever-other-value';
        $this->config->set('health-check.access_token', $token);
        $this->request->expects()->query('token')->andReturn($token);
        $report = (new AggregateReport)->addCheckerReport(
            'bye-world',
            new CheckerReport(true)
        );
        $this->health_check_service_mock->expects()
            ->checkServices()
            ->andReturns($report);

        $response = $this->controller->checkHealth($this->request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
