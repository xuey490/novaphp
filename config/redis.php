<?php
// config/redis.php

return [
    'host'     =>  env('REDIS_HOST') ?? '127.0.0.1',
    'port'     => (int) (env('REDIS_PORT') ?? 6379),
    'database' => (int) (env('REDIS_DB') ?? 0),
    'password' => env('REDIS_PASSWORD') ?? null,
    'timeout'  => 3,
];