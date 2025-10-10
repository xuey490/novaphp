<?php
// config/redis.php
return [
    'host'     => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
    'port'     => (int) ($_ENV['REDIS_PORT'] ?? 6379),
    'database' => (int) ($_ENV['REDIS_DB'] ?? 0),
    'password' => $_ENV['REDIS_PASSWORD'] ?? null,
    'timeout'  => 2.5,
];