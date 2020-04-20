<?php
declare(strict_types=1);

return [
    'checkers' => [
        \CountlessIntegers\LaravelHealthCheck\Checkers\CacheChecker::class,
        \CountlessIntegers\LaravelHealthCheck\Checkers\DbChecker::class,
        \CountlessIntegers\LaravelHealthCheck\Checkers\LogFileChecker::class,
        \CountlessIntegers\LaravelHealthCheck\Checkers\StorageChecker::class => [
            'drives' => [
                'data_storage',
            ],
        ],
        \CountlessIntegers\LaravelHealthCheck\Checkers\DiskSpaceChecker::class => [
            'min_free_space' => '2GB',
        ],
        \CountlessIntegers\LaravelHealthCheck\Checkers\DynamodbChecker::class,
        \CountlessIntegers\LaravelHealthCheck\Checkers\SQSChecker::class => [
            'queue_url' => '',
        ],
        \CountlessIntegers\LaravelHealthCheck\Checkers\PingChecker::class => [
            'domains' => [
                '1.1.1.1',
                'https://google.com',
            ],
            'timeout' => 5,
        ],
    ],
    'access_token' => null,
];
