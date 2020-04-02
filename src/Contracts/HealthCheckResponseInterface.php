<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

interface HealthCheckResponseInterface extends Arrayable, Jsonable
{
    public function isHealthy(): bool;
    public function getReport(): array;
}
