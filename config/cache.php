<?php

// config/cache.php
return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
			'type' => 'File',  //兼容thinkcache
            'path' => '../storage/cache',
            'prefix' => 'cache_',           // ← 新增：key 前缀
            'enable_tags' => true,          // ← 新增：是否启用标签
        ],

        'redis' => [
            'driver' => 'redis',
			'type'   => 'Redis', //兼容thinkcache
			'host'       => '127.0.0.1',
			'port'	 => 6379,
            // redis密码
            'password'  => null,
			'expire'	=>3600,
			/*
            'connection' => [
                'scheme' => 'tcp',
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'port' => env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD', null),
            ],
			*/
            'database' => 0,
            'prefix' => 'redis_',           // Redis 缓存前缀
            'enable_tags' => true,
        ],

        'memcached' => [
            'driver' => 'memcached',
			'type'   => 'memcached',
            'servers' => [
                ['host' => env('MEMCACHED_HOST', '127.0.0.1'), 'port' => env('MEMCACHED_PORT', 11211)],
            ],
            'prefix' => 'mem_',
            'enable_tags' => true,
        ],
		
		
        'apcu' => [
            'driver' => 'apcu',
            'prefix' => 'apcu_',
            'enable_tags' => false,         // APCu 不适合大量标签（可选）
        ],

        'array' => [
            'driver' => 'array',
            'prefix' => 'array_',
            'enable_tags' => true,
        ],
    ],
];


