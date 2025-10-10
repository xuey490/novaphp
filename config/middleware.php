<?php

// config/middleware.php

return [
    /*
    |--------------------------------------------------------------------------
    | 全局中间件配置
    |--------------------------------------------------------------------------
    | 控制哪些中间件启用，以及它们的参数
    */

    'csrf_protection' => [
        'enabled' => true,
        'token_name' => '_token',
        'except' => [
            '/api/*',
            '/webhook/*',
            '/payment/notify'
        ],
        'error_message' => '请求无效，请刷新页面后重试。',
        'remove_after_validation' => true, // 用完即焚
    ],

    'referer_check' => [
        'enabled' => true,
        'allowed_hosts' => [
            'localhost',
            '127.0.0.1',
            'yourdomain.com',
            'sub.yourdomain.com'
        ],
        'allowed_schemes' => ['http', 'https'],
        'except' => [
            '/api/*',
            '/payment/*'
        ],
        'strict' => false, // false = 允许空 Referer（如隐私模式）
        'error_message' => '请求来源不被允许。',
    ],

    // 可扩展其他中间件
    // 'rate_limit' => [...]
];