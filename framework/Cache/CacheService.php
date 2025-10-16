<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Cache;

use think\cache\Driver; // ✅ 正确的入口类（替代 Store）
use think\facade\Cache;

// 注意命名空间

/*
$cache = getService('cache');
$cache->set('key', 'hello world!', 3600);
echo $cache->get('key');

*/

class CacheService
{
    protected array $config;

    protected array $instances = [];

    protected string $default;

    public function __construct(array $config)
    {
        $this->config  = $config;
        $this->default = $config['default'] ?? 'file';
    }

    /**
     * 快捷方法：直接调用默认缓存.
     * @param mixed $method
     * @param mixed $args
     */
    public function __call($method, $args)
    {
        return $this->store()->{$method}(...$args);
    }

    /**
     * 获取指定名称的缓存实例.
     */
    public function store(?string $name = null): CacheInterface
    {
        $name = $name ?? $this->default;

        if (! isset($this->instances[$name])) {
            $this->instances[$name] = $this->resolve($name);
        }

        return $this->instances[$name];
    }

    /**
     * 解析并创建缓存驱动.
     */
    protected function resolve(string $name): CacheInterface
    {
        if (! isset($this->config['stores'][$name])) {
            throw new \InvalidArgumentException("Cache store [{$name}] is not defined.");
        }

        $config = $this->config['stores'][$name];

        // ✅ 使用 think\Cache::connect() 替代 Store::connect()
        $driver = Cache::connect($config);

        // 包装为接口实现
        return new class($driver) implements CacheInterface {
            public function __construct(protected Driver $driver) {}

            public function get($key, $default = null)
            {
                return $this->driver->get($key, $default);
            }

            public function set($key, $value, $ttl = null)
            {
                return $this->driver->set($key, $value, $ttl);
            }

            public function delete($key)
            {
                return $this->driver->delete($key);
            }

            public function has($key)
            {
                return $this->driver->has($key);
            }

            public function clear()
            {
                return $this->driver->clear();
            }

            public function tag(array|string $name): TaggedCacheInterface
            {
                $tagSet = $this->driver->tag($name); // 返回 think\cache\TagSet
                return new TaggedCacheProxy($tagSet);
            }
        };
    }
}
