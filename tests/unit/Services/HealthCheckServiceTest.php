<?php

declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Services;

use CountlessIntegers\LaravelHealthCheck\Checkers\DiskSpaceChecker;
use CountlessIntegers\LaravelHealthCheck\Checkers\LogFileChecker;
use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use CountlessIntegers\Tests\AppTestCase;
use Illuminate\Support\Facades\Config;

class HealthCheckServiceTest extends AppTestCase
{
    /** @test */
    public function itCanReportHealth(): void
    {
        Config::set(
            'health-check.checkers',
            [
                DiskSpaceChecker::class => [
                    'min_free_space' => '1 MB',
                ],
            ],
        );
        $service = $this->app->make(HealthCheckService::class);

        $report = $service->checkServices();

        $this->assertTrue($report->isHealthy(), 'It should be healthy, but got ' . json_encode($report));
        $details = $report->getDetails();
        // JsonType check would be nicer for this, but for now it's broken in codeception, so:
        $this->assertArrayHasKey(DiskSpaceChecker::class, $details);
        $this->assertArrayHasKey('is_healthy', $details[DiskSpaceChecker::class]);
        $this->assertArrayHasKey('report', $details[DiskSpaceChecker::class]);
        $this->assertEquals(true, $details[DiskSpaceChecker::class]['is_healthy']);
    }

    /** @test */
    public function itCanReportLackOfHealth(): void
    {
        Config::set(
            'health-check.checkers',
            [
                LogFileChecker::class => [
                    'log_path' => '/not-accessible',
                ],
            ],
        );
        $service = $this->app->make(HealthCheckService::class);

        $report = $service->checkServices();

        $this->assertFalse($report->isHealthy());
        $details = $report->getDetails();
        // JsonType check would be nicer for this, but for now it's broken in codeception, so:
        $this->assertArrayHasKey(LogFileChecker::class, $details);
        $this->assertArrayHasKey('is_healthy', $details[LogFileChecker::class]);
        $this->assertArrayHasKey('report', $details[LogFileChecker::class]);
        $this->assertEquals(false, $details[LogFileChecker::class]['is_healthy']);
    }

    /** @test */
    public function itWillNotCrashBecauseOfAChecker(): void
    {
        Config::set(
            'health-check.checkers',
            [
                DiskSpaceChecker::class => [
                    'min_free_space' => '1 UnsupportedUnit',
                ],
            ],
        );
        $service = $this->app->make(HealthCheckService::class);

        $report = $service->checkServices();

        $this->assertFalse($report->isHealthy());
        $details = $report->getDetails();
        $this->assertArrayHasKey(DiskSpaceChecker::class, $details);
        $this->assertArrayHasKey('exception', $details[DiskSpaceChecker::class]['report']);
        $this->assertArrayHasKey('exception_message', $details[DiskSpaceChecker::class]['report']);
    }
}
