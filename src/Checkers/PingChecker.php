<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\AggregateReport;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use InvalidArgumentException;

/**
 * Will check if provided domains are reachable from the app host.
 */
class PingChecker implements HealthCheckerInterface
{
    private const DEFAULT_TIMEOUT = 5;

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        if (empty($config['domains'])) {
            throw new InvalidArgumentException(
                '`domains` (type: []string) configuration param is required'
            );
        }
        $this->config = $config;
    }

    public function checkHealth(): HealthCheckReportInterface
    {
        $report = new AggregateReport();
        foreach ($this->config['domains'] as $domain) {
            $report->addCheckerReport($domain, $this->pingDomain($domain));
        }
        return $report;
    }

    private function pingDomain(string $domain): CheckerReport
    {
        $port = 80;
        if (strpos($domain, 'https') === 0) {
            $port = 443;
        }
        $domain = preg_replace('@https?//@', '', $domain);
        $start_time = microtime(true);
        $file = fsockopen(
            $domain,
            $port,
            $error_number,
            $error_message,
            $this->config['timeout'] ?? self::DEFAULT_TIMEOUT
        );
        $stop_time  = microtime(true);
        $status = [];
        if ($file) {
            $status['response_time'] = floor(($stop_time - $start_time) * 1000);
        } else {
            $status['error'] = $error_message;
        }
        fclose($file);
        return new CheckerReport((bool)$file, $status);
    }
}
