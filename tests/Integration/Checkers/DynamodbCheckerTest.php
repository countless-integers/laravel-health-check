<?php

declare(strict_types=1);

namespace Tests\Integration\Checkers;

use CountlessIntegers\LaravelHealthCheck\Checkers\DynamodbChecker;
use CountlessIntegers\Tests\AppTestCase;
use Illuminate\Support\Facades\Config;

class DynamodbCheckerTest extends AppTestCase
{
    protected $loadEnvironmentVariables = true;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set(
            'aws',
            [
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
                'region' => env('AWS_REGION'),
                'version' => 'latest',
            ]
        );
    }

    /** @test */
    public function itCanCheckAllTables(): void
    {
        $checker = new DynamodbChecker();

        $report = $checker->checkHealth();

        $this->assertFalse($report->isHealthy());
    }

    /** @test */
    public function itCanCheckTables(): void
    {
        $checker = new DynamodbChecker([
            'tables' => ['healthy-table'],
        ]);

        $report = $checker->checkHealth();

        $this->assertFalse($report->isHealthy());
    }
}
