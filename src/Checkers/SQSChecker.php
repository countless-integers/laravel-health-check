<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use Aws\Laravel\AwsFacade;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use InvalidArgumentException;

/**
 * Will check SQS connection using default credentials taken by the SDK.
 *
 * AWS SDK description of fetching queue attributes:
 * @see: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#getqueueattributes
 *
 * ListTables API call does not use RCUs, aka it is free.
 */
class SQSChecker implements HealthCheckerInterface
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        if (empty($config['queue_url'])) {
            throw new InvalidArgumentException(
                '`queue_url` (type: string) configuration param is required'
            );
        }
        $this->config = $config;
    }

    #[\Override]
    public function checkHealth(): HealthCheckReportInterface
    {
        $client = AwsFacade::createClient('SQS');
        $response = $client->getQueueAttributes([
            'AttributeNames' => [
                'ApproximateNumberOfMessages',
                'ApproximateNumberOfMessagesDelayed',
                'ApproximateNumberOfMessagesNotVisible',
            ],
            'QueueUrl' => $this->config['queue_url'],
        ]);
        return new CheckerReport(true, [
            'approximate_number_of_messages' => $response['Attributes']['ApproximateNumberOfMessages'],
            'approximate_number_of_messages_delayed' => $response['Attributes']['ApproximateNumberOfMessagesDelayed'],
            'approximate_number_of_messages_in_flight' => $response['Attributes']['ApproximateNumberOfMessagesNotVisible'],
        ]);
    }
}
