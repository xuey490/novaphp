<?php

// config/cache.php
return [
    // 默认使用的缓存连接
    'default' => env('CACHE_DRIVER', 'file'),

    // 缓存驱动配置
    'stores' => [
        'file' => [
            'type' => 'File',
            'path' => __DIR__ . '/../storage/cache/',
            'expire' => 3600,
            'prefix' => 'cache_',
        ],
        'redis' => [
            'type' => 'Redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', ''),
            'select' => 0,
            'expire' => 3600,
            'prefix' => 'cache_',
        ],
        'memcache' => [
            'type' => 'Memcache',
            'host' => '127.0.0.1',
            'port' => 11211,
            'expire' => 3600,
            'prefix' => 'cache_',
        ],
        'wincache' => [
            'type' => 'WinCache',
            'prefix' => 'cache_',
            'expire' => 3600,
        ],
        'sqlite' => [
            'type' => 'Sqlite',
            'database' => __DIR__ . '/../storage/cache/cache.db',
            'expire' => 3600,
            'prefix' => 'cache_',
        ],
    ],
];