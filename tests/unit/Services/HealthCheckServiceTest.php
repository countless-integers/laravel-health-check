<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Services;

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

        $report = $service->checkServices();

        $this->assertTrue($report->isHealthy());
        $details = $report->getDetails();
        // JsonType check would be nicer for this, but for now it's broken in codeception, so:
        $this->assertArrayHasKey(DiskSpaceChecker::class, $details);
        $this->assertArrayHasKey('is_healthy', $details[DiskSpaceChecker::class]);
        $this->assertArrayHasKey('report', $details[DiskSpaceChecker::class]);
        $this->assertEquals(true, $details[DiskSpaceChecker::class]['is_healthy']);
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

        $report = $service->checkServices();

        $this->assertFalse($report->isHealthy());
        $details = $report->getDetails();
        // JsonType check would be nicer for this, but for now it's broken in codeception, so:
        $this->assertArrayHasKey(LogFileChecker::class, $details);
        $this->assertArrayHasKey('is_healthy', $details[LogFileChecker::class]);
        $this->assertArrayHasKey('report', $details[LogFileChecker::class]);
        $this->assertEquals(false, $details[LogFileChecker::class]['is_healthy']);
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

        $report = $service->checkServices();

        $this->assertFalse($report->isHealthy());
        $details = $report->getDetails();
        $this->assertArrayHasKey(DiskSpaceChecker::class, $details);
        $this->assertArrayHasKey('exception', $details[DiskSpaceChecker::class]['report']);
        $this->assertArrayHasKey('exception_message', $details[DiskSpaceChecker::class]['report']);
    }
}
