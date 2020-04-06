<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Checkers;

use Codeception\Test\Unit;
use CountlessIntegers\LaravelHealthCheck\Checkers\DbConnectionChecker;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Mockery;

class DbConnectionCheckerTest extends Unit
{

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $db;

    protected function _before()
    {
        $app = new Container();
        $app->singleton('app', Container::class);
        $this->db = Mockery::mock();
        $app->singleton('db', function () {
            return $this->db;
        });
        Facade::setFacadeApplication($app);
    }

    /**
     * @test
     *
     * The sorcery here is about capturing a randomly assigned values
     * and "passing" them to following exceptions. Assertions are not
     * synchronous here, so we need to call the `checkHealth` method
     * before actually checking argument values.
     */
    public function itWillSucceedIfAKeyCanBeSetAndRetrieved(): void
    {
        $checker = new DbConnectionChecker([
            'query' => 'SHOW TABLES;'
        ]);

        $this->db->expects()
            ->select()
            ->with('SHOW TABLES;')
            ->andReturn([
                'table',
            ]);

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
        $this->assertEmpty($report->getDetails());
    }
}
