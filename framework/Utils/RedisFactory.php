<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: RedisFactory
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;


/**
 * 工厂方法：创建 Redis 客户端
 */
class RedisFactory {
	
    public static function createRedisClient(array $config): \Redis {
        $redis = new \Redis();
        $connected = $redis->connect($config['host'], $config['port'], $config['timeout']);
        if (!$connected) {
            throw new RuntimeException('Failed to connect to Redis');
        }
        if (!empty($config['password'])) {
            $redis->auth($config['password']);
        }
        if (isset($config['database'])) {
            $redis->select($config['database']);
        }
        return $redis;
    }
}
