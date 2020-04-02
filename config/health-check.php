<?php
declare(strict_types=1);

return [
    'checkers' => [
        \CountlessIntegers\LaravelHealthCheck\Checkers\CacheConnectionChecker::class,
        \CountlessIntegers\LaravelHealthCheck\Checkers\DbConnectionChecker::class,
        \CountlessIntegers\LaravelHealthCheck\Checkers\LogFileChecker::class,
        \CountlessIntegers\LaravelHealthCheck\Checkers\StorageChecker::class => [
            'drives' => [
                'data_storage',
            ],
        ],
        \CountlessIntegers\LaravelHealthCheck\Checkers\DiskSpaceChecker::class => [
            'min_free_space' => '2GB',
        ],
    ],
];
