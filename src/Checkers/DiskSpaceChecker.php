<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Checkers;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckerInterface;
use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckReportInterface;
use CountlessIntegers\LaravelHealthCheck\Reports\CheckerReport;
use Illuminate\Database\QueryException;
use InvalidArgumentException;

class DiskSpaceChecker implements HealthCheckerInterface
{
    private const UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function checkHealth(): HealthCheckReportInterface
    {
        $min_required_disk_space = $this->config['min_free_space'];
        if (!is_int($min_required_disk_space)) {
            $min_required_disk_space = $this->humanToBytes($min_required_disk_space);
        }
        $drive_path = $this->config['drive_path'] ?? '.';
        try {
            $free_disk_space = (int)disk_free_space($drive_path);
            return new CheckerReport(
                $free_disk_space > $min_required_disk_space,
                [
                    'free_disk_space' => $this->bytesToHuman($free_disk_space),
                ]
            );
        } catch (QueryException $exception) {
            return new CheckerReport(false, [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function bytesToHuman(int $bytes): string
    {
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . self::UNITS[$i];
    }

    private function humanToBytes(string $human_readable_size): int
    {
        $power = count(self::UNITS);
        $matched_unit = null;
        foreach (array_reverse(self::UNITS) as $unit) {
            $power--;
            if (stripos($human_readable_size, $unit) !== false) {
                $matched_unit = $unit;
                break;
            }
        }
        if (!$matched_unit) {
            $units_pattern = implode('|', self::UNITS);
            throw new InvalidArgumentException(
                "$human_readable_size is not a supported size format. Supported values follow the pattern: "
                . "[0-9.]+($units_pattern)"
            );
        }
        $value = (float)trim(str_ireplace($matched_unit, '', $human_readable_size));
        return (int)(1024 ** $power * $value);
    }
}
