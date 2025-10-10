<?php

// Framework/Cache/TaggedCacheInterface.php
// 仅 ThinkPHP 风格

namespace Framework\Cache;

interface TaggedCacheInterface
{
    public function get($key, $default = null);
    public function set($key, $value, $ttl = null);
    public function delete($key); // 注意：ThinkPHP 用 delete/rm，我们统一用 delete
    public function clear();      // 清除该标签下所有缓存
}
