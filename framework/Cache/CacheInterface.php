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

interface CacheInterface
{
    public function get($key, $default = null);

    public function set($key, $value, $ttl = null);

    public function delete($key);

    public function has($key);

    public function clear();

    // ✅ 新增 tag 方法
    public function tag(array|string $name): TaggedCacheInterface;
}
