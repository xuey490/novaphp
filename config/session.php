<?php
// config/session.php

return [
    'storage_type' => env('SESSION_STORAGE') ?? 'redis', // 'file' 或 'redis'

    'options' => [
        'cookie_secure'   => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'lax',
        'use_cookies'     => true,
        'gc_maxlifetime'  => 1440, // 24分钟
        'gc_probability'  => 1,
        'gc_divisor'      => 100,
    ],
];