<?php

declare(strict_types=1);

namespace Tests\Unit\Checkers;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Laravel\AwsFacade;
use Aws\Result;
use CountlessIntegers\LaravelHealthCheck\Checkers\DynamodbChecker;
use Tests\AppTestCase;
use Mockery;

class DynamodbCheckerTest extends AppTestCase
{
    private DynamoDbClient $client_mock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client_mock = Mockery::mock(DynamoDbClient::class);
        AwsFacade::shouldReceive('createClient')
            ->with('DynamoDb')
            ->once()
            ->andReturn($this->client_mock);
    }

    /** @test */
    public function itCanPassUsingAllTables(): void
    {
        $result = new Result(['TableNames' => ['my-table']]);
        $this->client_mock->expects('listTables')
            ->andReturns($result);
        $checker = new DynamodbChecker();

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
    }

    /** @test */
    public function itCanPassUsingIndividualTables(): void
    {
        $healthy_table = 'healthy-table';
        $result = new Result([
            'Table' => [
                'TableStatus' => 'ACTIVE',
            ],
        ]);
        $this->client_mock->expects('describeTable')
            ->with(['TableName' => $healthy_table])
            ->andReturns($result);
        $checker = new DynamodbChecker([
            'tables' => [$healthy_table],
        ]);

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
    }

    /** @test */
    public function itWillFailIfOneOfTheTablesIsNotHealthy(): void
    {
        $fake_table_name = 'fake-table';
        $healthy_table = 'healthy-table';
        $this->client_mock->expects('describeTable')
            ->with(['TableName' => $fake_table_name])
            ->andReturns(new Result([
                'Table' => [
                    'TableStatus' => 'INACTIVE',
                ],
            ]));
        $this->client_mock->expects('describeTable')
            ->with(['TableName' => $healthy_table])
            ->andReturns(new Result([
                'Table' => [
                    'TableStatus' => 'ACTIVE',
                ],
            ]));
        $checker = new DynamodbChecker([
            'tables' => [$fake_table_name, $healthy_table],
        ]);

        $report = $checker->checkHealth();

        $this->assertFalse($report->isHealthy());
        $details = $report->getDetails();
        $this->assertFalse($details['tables'][$fake_table_name]['is_healthy']);
        $this->assertTrue($details['tables'][$healthy_table]['is_healthy']);
    }

    /** @test */
    public function itWillFailIfTableIsNotFound(): void
    {
        $exception_mock = Mockery::mock(DynamoDbException::class);
        $exception_mock->expects('getAwsErrorCode')
            ->andReturns('ResourceNotFound');
        $mia_table = 'fake-table';
        $this->client_mock->expects('describeTable')
            ->with(['TableName' => $mia_table])
            ->andThrows($exception_mock);
        $checker = new DynamodbChecker([
            'tables' => [$mia_table],
        ]);

        $report = $checker->checkHealth();

        $this->assertFalse($report->isHealthy());
        $details = $report->getDetails();
        $this->assertFalse($details['tables'][$mia_table]['is_healthy']);
        $this->assertEquals($details['tables'][$mia_table]['reason'], 'ResourceNotFound');
    }
}
