<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Checkers;

use Codeception\Test\Unit;
use CountlessIntegers\LaravelHealthCheck\Checkers\PingChecker;
use InvalidArgumentException;

class PingCheckerTest extends Unit
{
    /**
     * @test
     * @fixme: this is actually opening a connection to a real server
     */
    public function itWillSucceedIfAKeyCanBeSetAndRetrieved(): void
    {
        $checker = new PingChecker([
            'domains' => [
                // CloudFlare DNS server
                '1.1.1.1',
            ],
        ]);

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
    }

    /**
     * @test
     * @dataProvider provideInvalidConfig
     *
     * @param array $invalid_config
     */
    public function itCrashIfNoDomainsProvided(array $invalid_config): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PingChecker($invalid_config);
    }

    public function provideInvalidConfig(): array
    {
        return [
            'empty_domains' => [[
                'domains' => [],
            ]],
            'no_domains' => [[]],
        ];
    }
}
