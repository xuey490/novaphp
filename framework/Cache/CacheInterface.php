<?php

// Framework/Cache/CacheInterface.php

namespace Framework\Cache;

interface CacheInterface
{
    public function get($key, $default = null);
    public function set($key, $value, $ttl = null);
    public function delete($key);
    public function has($key);
    public function clear();

    // ✅ 新增 tag 方法
    public function tag(string|array $name): TaggedCacheInterface;
}
