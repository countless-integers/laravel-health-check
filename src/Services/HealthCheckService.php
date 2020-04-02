<?php
declare(strict_types=1);

namespace CountlessIntegers\LaravelHealthCheck\Services;

use CountlessIntegers\LaravelHealthCheck\Contracts\HealthCheckResponseInterface;
use CountlessIntegers\LaravelHealthCheck\Responses\DefaultResponse;
use CountlessIntegers\LaravelHealthCheck\Responses\ServiceResponse;
use Illuminate\Support\Facades\App;
use Throwable;

class HealthCheckService
{
    /**
     * @var array
     */
    private $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function checkServices(): HealthCheckResponseInterface
    {
        $response = new ServiceResponse();
        foreach ($this->config['checkers'] as $key => $value) {
            $checker_class = $value;
            $checker_config = [];
            if (is_array($value)) {
                $checker_class = $key;
                $checker_config = $value;
            }
            try {
                $checker = App::make($checker_class, ['config' => $checker_config]);
                $response->addCheckerReport($checker_class, $checker->checkHealth());
            } catch (Throwable $exception) {
                $response->addCheckerReport($checker_class, new DefaultResponse(false, [
                    'exception' => get_class($exception),
                    'exception_message' => $exception->getMessage(),
                ]));
            }
        }
        return $response;
    }
}
