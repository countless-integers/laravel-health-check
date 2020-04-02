<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Services;

use Codeception\PHPUnit\Constraint\JsonType;
use Codeception\Test\Unit;
use CountlessIntegers\LaravelHealthCheck\Checkers\DiskSpaceChecker;
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
        $app['config']->set('app.health-checks.checkers', [
            DiskSpaceChecker::class => [
                'min_free_space' => '1 MB',
            ]
        ]);
        Facade::setFacadeApplication($app);

        $this->service = new HealthCheckService();
    }

    /**
     * @test
     */
    public function itCanReportHealth(): void
    {
        $response = $this->service->checkServices();

        $this->assertTrue($response->isHealthy());
        $report = $response->getReport();
//        $spec = new JsonType($report);
//        $this->assertTrue($spec->evaluate([
//            'is_healthy' => 'bool',
//        ]));
    }
}
