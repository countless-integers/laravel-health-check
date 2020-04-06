<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Checkers;

use Carbon\Carbon;
use Codeception\Test\Unit;
use CountlessIntegers\LaravelHealthCheck\Checkers\CacheChecker;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Mockery;

class CacheCheckerTest extends Unit
{

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $cacheMock;

    protected function _before()
    {
        $app = new Container();
        $app->singleton('app', Container::class);
        $this->cacheMock = Mockery::mock();
        $app->singleton('cache', function () {
            return $this->cacheMock;
        });
        Facade::setFacadeApplication($app);
        codecept_debug('ping');
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
        $checker = new CacheChecker();

        $this->cacheMock->expects()
            ->put()
            ->with(
                Mockery::capture($key),
                Mockery::capture($value),
                Mockery::type(Carbon::class)
            );

        $this->cacheMock->expects()
            ->get()
            ->with(Mockery::capture($get_key))
            ->andReturnUsing(static function () use (&$value) {
                return $value;
            })
        ;

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
        $this->assertEmpty($report->getDetails());
        $this->assertEquals($key, $get_key);
        $this->assertNotEquals($key, $value);
    }

    /**
     * @fixme: there's something wrong with mockery expectations here:
     *         when both tests run they seem to inherit expectation return values.
     * @skip
     * @test
     */
    public function itWillFailIfTheSetKeyDoesNotMatchTheRetrievedOne(): void
    {
        $checker = new CacheChecker();

        $this->cacheMock->expects()
            ->put()
            ->with(
                Mockery::capture($key),
                Mockery::capture($value),
                Mockery::type(Carbon::class)
            );

        $this->cacheMock->expects()
            ->get()
            ->with(Mockery::capture($get_key))
            ->andReturn('some-value')
        ;

        $report = $checker->checkHealth();

        $this->assertFalse($report->isHealthy());
        $this->assertEmpty($report->getDetails());
        $this->assertEquals($key, $get_key);
        $this->assertNotEquals('some-value', $value);
    }
}
