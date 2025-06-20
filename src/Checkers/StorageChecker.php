<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use Illuminate\Support\Facades\Storage;

class StorageChecker implements HealthCheckerInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $report = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    #[\Override]
    public function checkHealth(): HealthCheckReportInterface
    {
        $drives = $this->config['drives'];
        $test_file = uniqid('health-check-file-', true);
        $this->report = [];
        foreach ($drives as $drive) {
            $this->report[$drive] = [
                'is_writable' => Storage::disk($drive)->put($test_file, time()),
                'path' => $test_file,
                'url' => Storage::disk($drive)->url($test_file),
            ];
        }
        return new CheckerReport(true, $this->report);
    }

    /**
     * Handles clean-up
     */
    public function __destruct()
    {
        foreach ($this->report as $drive => $report) {
            if (empty($report['path'])) {
                continue;
            }
            Storage::disk($drive)->delete($report['path']);
        }
    }
}
