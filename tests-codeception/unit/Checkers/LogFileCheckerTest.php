<?php
declare(strict_types=1);

namespace CountlessIntegers\UnitTests\Checkers;

use Codeception\Test\Unit;
use CountlessIntegers\LaravelHealthCheck\Checkers\LogFileChecker;

class LogFileCheckerTest extends Unit
{
    /**
     * @test
     * @dataProvider provideInvalidLogPaths
     *
     * @param array $config
     */
    public function itWillFailIfTheLogFilePathIsInvalid(array $config): void
    {
        $checker = new LogFileChecker($config);

        $report = $checker->checkHealth();

        $this->assertFalse($report->isHealthy());
        $this->assertEmpty($report->getDetails());
    }

    public function provideInvalidLogPaths(): array
    {
        return [
            'empty_path' => [[
                'log_path' => '',
            ]],
            'non_existing_path' => [[
                'log_path' => '/random',
            ]],
        ];
    }

    /**
     * @test
     */
    public function itWillReportHealthIfTheLogFilePathIsWritable(): void
    {
        $checker = new LogFileChecker([
            'log_path' => codecept_data_dir('logs/dummy.log')
        ]);

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
        $this->assertEmpty($report->getDetails());
    }
}
