<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: SessionServiceProvider.php
 * @Date: 2025-11-06
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Session;

use Framework\Session\FileSessionHandler;
use Framework\Session\RedisGroupSessionHandler;
use Framework\Utils\RedisFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class SessionServiceProvider
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services()
            ->defaults()
            ->autowire()
            ->autoconfigure();

        // === 1. 加载配置 ===
        $redisConfig = require \dirname(__DIR__, 2) . '/config/redis.php';
        $sessionConfig = require \dirname(__DIR__, 2) . '/config/session.php';

        $storageType     = $sessionConfig['storage_type'] ?? 'file';
        $sessionOptions  = $sessionConfig['options'] ?? [];
        $fileSavePath    = $sessionConfig['file_save_path'] ?? '%kernel.project_dir%/storage/sessions';
        $groupPrefix     = $sessionConfig['redis']['group_prefix'] ?? 'session:default';
        $ttl             = $sessionConfig['redis']['ttl'] ?? 3600;

        // === 2. 注册 Redis 客户端（只有 Redis 模式需要）===
        if (in_array($storageType, ['redis', 'redis_grouped'], true)) {
            $services->set('redis.client', \Redis::class)
                ->factory([RedisFactory::class, 'createRedisClient'])
                ->args([$redisConfig])
                ->public();
        }

        // === 3. 注册 Session Handler & Storage ===
        switch ($storageType) {
            case 'redis_grouped':
                // ✅ 使用分组 Redis 存储
                $services->set('session.handler', RedisGroupSessionHandler::class)
                    ->args([
                        service('redis.client'),
                        [
                            'group_prefix' => $groupPrefix,
                            'ttl' => $ttl,
							'prefix' => 'sess_',         // 普通前缀
							'locking'        => true,          // 启用显式锁（默认 true）
							'spin_lock_wait' => 150000,        // 自旋等待 microseconds
							'lock_ttl'       => 30000,         // 锁过期时间 ms（用于 SET PX）
                        ],
                    ])
                    ->public();

                $services->set('session.storage', NativeSessionStorage::class)
                    ->args([$sessionOptions, service('session.handler')])
                    ->public();
                break;

            case 'redis':
                // ✅ 普通 Redis 存储
                $services->set('session.handler', RedisSessionHandler::class)
                    ->args([
                        service('redis.client'),
                        [
                            'prefix' => 'redis_session_',
                            'ttl' => $ttl,
                        ],
                    ])
                    ->public();

                $services->set('session.storage', NativeSessionStorage::class)
                    ->args([$sessionOptions, service('session.handler')])
                    ->public();
                break;

            case 'file':
            default:
                // ✅ 文件存储（使用你写的 FileSessionHandler）
                $services->set('session.handler.custom_file', FileSessionHandler::class)
                    ->call('setSavePath', [$fileSavePath])
                    ->call('setPrefix', [$sessionOptions['name'] ?? 'sess'])
                    ->public();

                $services->set('session.handler', StrictSessionHandler::class)
                    ->args([service('session.handler.custom_file')])
                    ->public();

                $services->set('session.storage', NativeSessionStorage::class)
                    ->args([$sessionOptions, service('session.handler')])
                    ->public();
                break;
        }

        // === 4. 注册 Session 服务 ===
        $services->set('session', Session::class)
            ->args([service('session.storage')])
            ->public();
    }
}
