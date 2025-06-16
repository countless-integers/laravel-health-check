<?php

declare(strict_types=1);

namespace Tests\Integration\Checkers;

use CountlessIntegers\LaravelHealthCheck\Checkers\DynamodbChecker;
use Tests\AppTestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class DynamodbCheckerTest extends AppTestCase
{
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
    #[Test]
    public function itCanPassUsingAllTables(): void
    {
        $checker = new DynamodbChecker();

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
    }

    /** @test */
    #[Test]
    public function itCanPassUsingIndividualTables(): void
    {
        $checker = new DynamodbChecker([
            'tables' => [env('AWS_DYNAMODB_TABLE_NAME')],
        ]);

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
    }

    /** @test */
    #[Test]
    public function itWillFailIfOneOfTheTablesIsNotHealthy(): void
    {
        $fake_table_name = 'fake-table';
        $checker = new DynamodbChecker([
            'tables' => [$fake_table_name, env('AWS_DYNAMODB_TABLE_NAME')],
        ]);

        $report = $checker->checkHealth();

        $this->assertFalse($report->isHealthy());
        $details = $report->getDetails();
        $this->assertFalse($details['tables'][$fake_table_name]['is_healthy']);
        $this->assertTrue($details['tables'][env('AWS_DYNAMODB_TABLE_NAME')]['is_healthy']);
    }
}
