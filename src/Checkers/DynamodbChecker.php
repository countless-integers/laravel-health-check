<?php

declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use Aws\Laravel\AwsFacade;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;

/**
 * Will check DynamoDB connection using default credentials taken by the SDK
 * from the app. If there's a connection issue, SDK will is supposed to throw
 * an exception.
 *
 * ListTables API call does not use RCUs, aka it is free.
 *
 * AWS PHP SDK description of ListTables:
 * @see: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-dynamodb-2012-08-10.html#listtables
 * 
 * AWS PHP SDK docs for DescribeTable:
 * @see: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-dynamodb-2012-08-10.html#describetable
 * AWS API docs for DescribeTable:
 * @see: https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeTable.html
 */
class DynamodbChecker implements HealthCheckerInterface
{
    private ?DynamoDbClient $client;

    public function __construct(private array $config = [])
    {
    }

    public function checkHealth(): HealthCheckReportInterface
    {
        $this->client = AwsFacade::createClient('DynamoDb');
        if (isset($this->config['tables']) && is_array($this->config['tables'])) {
            return $this->checkByQueryingTables($this->config['tables']);
        }
        return $this->checkByListingAllTables();
    }

    private function checkByListingAllTables(): HealthCheckReportInterface
    {
        $result = $this->client->listTables([
            'Limit' => 1,
        ]);
        $tables = $result['TableNames'] ?? [];

        return new CheckerReport(count($tables) === 1);
    }

    private function checkByQueryingTables(array $table_names): HealthCheckReportInterface
    {
        $is_healthy = true;
        $details = ['tables' => []];
        foreach ($table_names as $table_name) {
            try {
                $result = $this->client->describeTable([
                    'TableName' => $table_name
                ]);
                $is_table_healthy = ($result['Table']['TableStatus'] ?? null) === 'ACTIVE';
                $details['tables'][$table_name] = [
                    'is_healthy' => $is_table_healthy,
                ];
                if ($is_table_healthy === false) {
                    $is_healthy = false;
                }
            } catch (DynamoDbException $e) {
                $details['tables'][$table_name] = [
                    'is_healthy' => false,
                    'reason' => $e->getAwsErrorCode(),
                ];
                $is_healthy = false;
            }
        }
        return new CheckerReport($is_healthy, $details);
    }
}
