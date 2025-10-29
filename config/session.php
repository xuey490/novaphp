<?php
// config/session.php

return [
    'storage_type' => env('SESSION_STORAGE') ?? 'redis', // 'file' 或 'redis'

    'options' => [
        'cookie_secure'   => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'lax',
        'use_cookies'     => true,
        'gc_maxlifetime'  => 3600, // 单位秒
        'gc_probability'  => 1,
        'gc_divisor'      => 100,
		'name'            => 'file_session_', // ← 这就是 session cookie 名称（前缀）
    ],
    // 新增：仅用于 file 存储的路径
    'file_save_path' => __DIR__ . '/../storage/sessions',
];