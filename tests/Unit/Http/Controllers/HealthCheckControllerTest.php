<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use CountlessIntegers\LaravelHealthCheck\Reports\AggregateReport;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use Tests\AppTestCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class HealthCheckControllerTest extends AppTestCase
{
    /** @test */
    #[Test]
    public function itWillReturnOkIfHealthCheckPasses(): void
    {
        $checks = [
            'healthy-check',
            'healthy-with-config' => [
                'something' => 'in the way',
            ],
        ];
        Config::set('health-check.checkers', $checks);
        $report = (new AggregateReport)
            ->addCheckerReport('healthy-check', new CheckerReport(true))
            ->addCheckerReport('healthy-with-config', new CheckerReport(true, ['uuum' => 'humm']));
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock
                ->expects('runChecks')
                ->with($checks)
                ->andReturns($report),
        );

        $response = $this->get(route('health-check'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'is_healthy' => true,
                'report' => [
                    'healthy-check' => [
                        'is_healthy' => true,
                        'report' => [],
                    ],
                    'healthy-with-config' => [
                        'is_healthy' => true,
                        'report' => [
                            'uuum' => 'humm',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    #[Test]
    public function itWillReturnServerErrorResponseIfHealthCheckFails(): void
    {
        $checks = [
            'healthy-check',
            'unhealthy-check',
        ];
        Config::set('health-check.checkers', $checks);
        $report = (new AggregateReport())
            ->addCheckerReport('healthy-check', new CheckerReport(true))
            ->addCheckerReport('unhealthy-check', new CheckerReport(false));
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock->expects('runChecks')
                ->with($checks)
                ->andReturns($report),
        );

        $response = $this->get(route('health-check'));

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson(
                [
                    'is_healthy' => false,
                    'report' => [
                        'healthy-check' => [
                            'is_healthy' => true,
                            'report' => [],
                        ],
                        'unhealthy-check' => [
                            'is_healthy' => false,
                            'report' => [],
                        ],
                    ],
                ],
            );
    }

    /** @test */
    #[Test]
    public function itWillReturn404IfAccessTokenSpecifiedButNotPresent(): void
    {
        Config::set('health-check.access_token', 'whatever-value');

        $response = $this->get(route('health-check'));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([]);
    }

    /** @test */
    #[Test]
    public function itWillReturnResponseIfAccessTokenSpecifiedAndPresent(): void
    {
        $checks = [
            'healthy-check',
        ];
        Config::set('health-check.checkers', $checks);
        $token = 'valid-token';
        Config::set('health-check.access_token', $token);
        $report = (new AggregateReport)->addCheckerReport(
            'healthy-check',
            new CheckerReport(true)
        );
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock->shouldReceive('runChecks')->andReturns($report),
        );

        $response = $this->get(route('health-check') . '?' . http_build_query(['token' => $token]));

        $response->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    #[Test]
    public function itWillPassIfExtendedChecksPass(): void
    {
        $checks = [
            'healthy-check',
        ];
        Config::set('health-check.checkers', $checks);
        $extended_checks = [
            'extended-check',
            'extended-with-config' => [
                'something' => 'something',
            ],
        ];
        Config::set('health-check.extended_checks', $extended_checks);
        $report = (new AggregateReport)
            ->addCheckerReport('healthy-check', new CheckerReport(true))
            ->addCheckerReport('extended-check', new CheckerReport(true))
            ->addCheckerReport('extended-with-config', new CheckerReport(true));
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock
                ->expects('runChecks')
                ->with(array_merge($checks, $extended_checks))
                ->andReturns($report),
        );

        $response = $this->get(route('health-check') . '?extended=true');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'is_healthy' => true,
                'report' => [
                    'healthy-check' => [
                        'is_healthy' => true,
                        'report' => [],
                    ],
                    'extended-check' => [
                        'is_healthy' => true,
                        'report' => [],
                    ],
                    'extended-with-config' => [
                        'is_healthy' => true,
                        'report' => [],
                    ],
                ],
            ]);
    }

    /** @test */
    #[Test]
    public function itWillFailIfExtendedChecksFail(): void
    {
        $checks = [
            'healthy-check',
        ];
        Config::set('health-check.checkers', $checks);
        $extended_checks = [
            'extended-check',
        ];
        Config::set('health-check.extended_checks', $extended_checks);
        $report = (new AggregateReport)
            ->addCheckerReport('healthy-check', new CheckerReport(true))
            ->addCheckerReport('extended-check', new CheckerReport(false));
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock
                ->expects('runChecks')
                ->with([...$checks, ...$extended_checks])
                ->andReturns($report),
        );

        $response = $this->get(route('health-check') . '?extended=true');

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'is_healthy' => false,
                'report' => [
                    'healthy-check' => [
                        'is_healthy' => true,
                        'report' => [],
                    ],
                    'extended-check' => [
                        'is_healthy' => false,
                        'report' => [],
                    ],
                ],
            ]);
    }

    /** @test */
    #[Test]
    public function itWillReturnOkIfNoChecksConfigured(): void
    {
        Config::set('health-check.checkers', []);
        $report = (new AggregateReport);
        $this->mock(
            HealthCheckService::class,
            fn (MockInterface $mock) => $mock
                ->expects('runChecks')
                ->with([])
                ->andReturns($report),
        );

        $response = $this->get(route('health-check'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'is_healthy' => true,
                'report' => [],
            ]);
    }
}
