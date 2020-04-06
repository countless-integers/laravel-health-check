<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use Aws\Laravel\AwsFacade;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;

/**
 * Will check DynamoDB connection using default credentials taken by the SDK
 * from the app. If there's a connection issue, SDK will is supposed to throw
 * an exception.
 *
 * Inspiration:
 * @see: https://forums.aws.amazon.com/thread.jspa?threadID=167259
 *
 * AWS SDK description of list tables:
 * @see: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-dynamodb-2012-08-10.html#listtables
 *
 * ListTables API call does not use RCUs, aka it is free.
 */
class DynamodbChecker implements HealthCheckerInterface
{
    public function checkHealth(): HealthCheckReportInterface
    {
        $client = AwsFacade::createClient('DynamoDb');
        $result = $client->listTables([
//            'ExclusiveStartTableName' => '<string>',
            'Limit' => 1,
        ]);
        $tables = $result['TableNames'] ?? [];
        return new CheckerReport(count($tables) === 1);
    }
}
