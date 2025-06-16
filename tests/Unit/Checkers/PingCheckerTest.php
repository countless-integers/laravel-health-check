<?php

declare(strict_types=1);

namespace Tests\Unit\Checkers;

use CountlessIntegers\LaravelHealthCheck\Checkers\PingChecker;
use Tests\AppTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PingCheckerTest extends AppTestCase
{
    /**
     * @test
     * @fixme: this is actually opening a connection to a real server
     */
    #[Test]
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
    #[Test]
    #[DataProvider('provideInvalidConfig')]
    public function itCrashIfNoDomainsProvided(array $invalid_config): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PingChecker($invalid_config);
    }

    public static function provideInvalidConfig(): array
    {
        return [
            'empty_domains' => [[
                'domains' => [],
            ]],
            'no_domains' => [[]],
        ];
    }
}
