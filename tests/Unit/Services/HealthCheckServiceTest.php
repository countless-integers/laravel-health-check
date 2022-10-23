<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use CountlessIntegers\LaravelHealthCheck\Checkers\DiskSpaceChecker;
use CountlessIntegers\LaravelHealthCheck\Checkers\LogFileChecker;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use Tests\AppTestCase;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Mockery\MockInterface;

class HealthCheckServiceTest extends AppTestCase
{
    /** @test */
    public function itCanRunChecks(): void
    {
        $checks = [
            DiskSpaceChecker::class => [
                'min_free_space' => '1 MB',
            ],
        ];
        Config::set('health-check.checkers', $checks);
        // @see: https://github.com/laravel/framework/issues/25401
        // @see: https://github.com/laravel/framework/issues/25041#issuecomment-445479867
        $this->app->offsetSet(
            DiskSpaceChecker::class,
            $this->mock(
                DiskSpaceChecker::class,
                fn (MockInterface $mock) => $mock
                    ->expects('checkHealth')
                    ->andReturns(
                        (new CheckerReport(true, ['free_disk_space' => '70.77 GB'])),
                    ),
            )
        );
        $service = $this->app->make(HealthCheckService::class);

        $report = $service->runChecks($checks);

        $this->assertTrue(
            $report->isHealthy(),
            'It should be healthy, but got ' . json_encode($report),
        );
        $this->assertEquals(
            [
                DiskSpaceChecker::class => [
                    'is_healthy' => true,
                    'report' => [
                        'free_disk_space' => '70.77 GB',
                    ],
                ]
            ],
            $report->getDetails(),
        );
    }

    /** @test */
    public function itCanReportLackOfHealth(): void
    {
        $checks = [
            LogFileChecker::class => [
                'log_path' => '/not-accessible',
            ],
        ];
        Config::set('health-check.checkers', $checks);
        $this->app->offsetSet(
            LogFileChecker::class,
            $this->mock(
                LogFileChecker::class,
                fn (MockInterface $mock) => $mock
                    ->expects('checkHealth')
                    ->andReturns(
                        (new CheckerReport(false, [])),
                    ),
            )
        );
        $service = $this->app->make(HealthCheckService::class);

        $report = $service->runChecks($checks);

        $this->assertFalse($report->isHealthy());
        $this->assertEquals(
            [
                LogFileChecker::class => [
                    'is_healthy' => false,
                    'report' => [],
                ]
            ],
            $report->getDetails(),
        );
    }

    /** @test */
    public function itWillNotCrashBecauseOfAChecker(): void
    {
        $checks = [
            DiskSpaceChecker::class => [
                'min_free_space' => '1 UnsupportedUnit',
            ],
        ];
        $exception_message = 'unsupported unit';
        Config::set('health-check.checkers', $checks);
        $this->app->offsetSet(
            DiskSpaceChecker::class,
            $this->mock(
                DiskSpaceChecker::class,
                fn (MockInterface $mock) => $mock
                    ->expects('checkHealth')
                    ->andThrows(new InvalidArgumentException($exception_message)),
            ),
        );
        $service = $this->app->make(HealthCheckService::class);


        $report = $service->runChecks($checks);

        $this->assertFalse($report->isHealthy());
        $this->assertEquals(
            [
                DiskSpaceChecker::class => [
                    'is_healthy' => false,
                    'report' => [
                        'exception' => 'InvalidArgumentException',
                        'exception_message' => $exception_message,
                    ],
                ]
            ],
            $report->getDetails(),
        );
    }
}
