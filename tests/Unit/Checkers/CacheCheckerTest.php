<?php

declare(strict_types=1);

namespace Tests\Unit\Checkers;

use Carbon\Carbon;
use CountlessIntegers\LaravelHealthCheck\Checkers\CacheChecker;
use Tests\AppTestCase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class CacheCheckerTest extends AppTestCase
{
    private CacheChecker $checker;

    public function setUp(): void
    {
        $this->checker = new CacheChecker();
    }

    /**
     * @test
     *
     * The sorcery here is about capturing a randomly assigned values
     * and "passing" them to following exceptions. Assertions are not
     * synchronous here, so we need to call the `checkHealth` method
     * before actually checking argument values.
     */
    #[Test]
    public function itWillSucceedIfAKeyCanBeSetAndRetrieved(): void
    {
        Cache::shouldReceive('put')
            ->once()
            ->with(
                Mockery::capture($key),
                Mockery::capture($value),
                Mockery::type(Carbon::class)
            );
        Cache::shouldReceive('get')
            ->once()
            ->with(Mockery::capture($get_key))
            ->andReturnUsing(static function () use (&$value) {
                return $value;
            });

        $report = $this->checker->checkHealth();

        $this->assertTrue($report->isHealthy());
        $this->assertEmpty($report->getDetails());
        $this->assertEquals($key, $get_key);
        $this->assertNotEquals($key, $value);
    }

    /** @test */
    #[Test]
    public function itWillFailIfTheSetKeyDoesNotMatchTheRetrievedOne(): void
    {
        Cache::shouldReceive('put')
            ->once()
            ->with(
                Mockery::capture($key),
                Mockery::capture($value),
                Mockery::type(Carbon::class)
            );
        Cache::shouldReceive('get')
            ->once()
            ->with(Mockery::capture($get_key))
            ->andReturn('some-value');

        $report = $this->checker->checkHealth();

        $this->assertFalse($report->isHealthy());
        $this->assertEmpty($report->getDetails());
        $this->assertEquals($key, $get_key);
        $this->assertNotEquals('some-value', $value);
    }
}
