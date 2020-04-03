<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Services;

use Codeception\PHPUnit\Constraint\JsonType;
use Codeception\Test\Unit;
use CountlessIntegers\LaravelHealthCheck\Checkers\DiskSpaceChecker;
use CountlessIntegers\LaravelHealthCheck\Checkers\LogFileChecker;
use CountlessIntegers\LaravelHealthCheck\Services\HealthCheckService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Config\Repository;

class HealthCheckServiceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var HealthCheckService
     */
    private $service;

    protected function _before()
    {
        $app = new Container();
        $app->singleton('app', Container::class);
        $app->singleton('config', Repository::class);
        Facade::setFacadeApplication($app);
    }

    /**
     * @test
     */
    public function itCanReportHealth(): void
    {
        $config = [
            'checkers' => [
                DiskSpaceChecker::class => [
                    'min_free_space' => '1 MB',
                ],
            ],
        ];
        $service = new HealthCheckService($config);

        $response = $service->checkServices();

        $this->assertTrue($response->isHealthy());
        $report = $response->getReport();
        // JsonType check would be nicer for this, but for now it's broken in codeception, so:
        $this->assertArrayHasKey(DiskSpaceChecker::class, $report);
        $this->assertArrayHasKey('is_healthy', $report[DiskSpaceChecker::class]);
        $this->assertArrayHasKey('report', $report[DiskSpaceChecker::class]);
        $this->assertEquals(true, $report[DiskSpaceChecker::class]['is_healthy']);
    }

    /**
     * @test
     */
    public function itCanReportLackOfHealth(): void
    {
        $config = [
            'checkers' => [
                LogFileChecker::class => [
                    'log_path' => '/not-accessible',
                ],
            ],
        ];
        $service = new HealthCheckService($config);

        $response = $service->checkServices();

        $this->assertFalse($response->isHealthy());
        $report = $response->getReport();
        // JsonType check would be nicer for this, but for now it's broken in codeception, so:
        $this->assertArrayHasKey(LogFileChecker::class, $report);
        $this->assertArrayHasKey('is_healthy', $report[LogFileChecker::class]);
        $this->assertArrayHasKey('report', $report[LogFileChecker::class]);
        $this->assertEquals(false, $report[LogFileChecker::class]['is_healthy']);
    }

    /**
     * @test
     */
    public function itWillNotCrashBecauseOfAChecker(): void
    {
        $config = [
            'checkers' => [
                DiskSpaceChecker::class => [
                    'min_free_space' => '1 UnsupportedUnit',
                ],
            ],
        ];
        $service = new HealthCheckService($config);

        $response = $service->checkServices();

        $this->assertFalse($response->isHealthy());
        $report = $response->getReport();
        $this->assertArrayHasKey(DiskSpaceChecker::class, $report);
        $this->assertArrayHasKey('exception', $report[DiskSpaceChecker::class]['report']);
        $this->assertArrayHasKey('exception_message', $report[DiskSpaceChecker::class]['report']);
    }
}
