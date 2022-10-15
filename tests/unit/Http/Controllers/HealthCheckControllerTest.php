<?php

declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Http\Controllers;

use CountlessIntegers\LaravelHealthCheck\Http\Controllers\HealthCheckController;
use CountlessIntegers\LaravelHealthCheck\Reports\AggregateReport;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use CountlessIntegers\Tests\AppTestCase;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Mockery;
use Mockery\MockInterface;

class HealthCheckControllerTest extends AppTestCase
{
    /** @test */
    public function itWillReturnOkIfHealthCheckPasses(): void
    {
        $report = new AggregateReport();
        $report->addCheckerReport('hello-world', new CheckerReport(true));
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock->shouldReceive('checkServices')->andReturns($report),
        );

        $response = $this->get(route('health-check'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'is_healthy' => true,
                'report' => [
                    'hello-world' => [
                        'is_healthy' => true,
                        'report' => [],
                    ],
                ],
            ]);
    }

    /** @test */
    public function itWillReturnServerErrorResponseIfHealthCheckFails(): void
    {
        $report = new AggregateReport();
        $report->addCheckerReport('hello-world', new CheckerReport(false));
        $report->addCheckerReport('bye-world', new CheckerReport(true));
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock->shouldReceive('checkServices')->andReturns($report),
        );

        $response = $this->get(route('health-check'));

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson(
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
            );
    }

    /** @test */
    public function itWillReturn404IfAccessTokenSpecifiedButNotPresent(): void
    {
        Config::set('health-check.access_token', 'whatever-value');

        $response = $this->get(route('health-check'));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function itWillReturnResponseIfAccessTokenSpecifiedAndPresent(): void
    {
        $token = 'whatever-other-value';
        Config::set('health-check.access_token', $token);
        $report = (new AggregateReport)->addCheckerReport(
            'bye-world',
            new CheckerReport(true)
        );
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock->shouldReceive('checkServices')->andReturns($report),
        );

        $response = $this->get(route('health-check') . '?' . http_build_query(['token' => $token]));

        $response->assertStatus(Response::HTTP_OK);
    }
}
