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

use think\cache\TagSet;

class TaggedCacheProxy implements TaggedCacheInterface
{
    public function __construct(protected TagSet $tagSet) {}

    public function get($key, $default = null)
    {
        return $this->tagSet->get($key, $default);
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->tagSet->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        // ThinkPHP 的 TagSet 使用 rm() 删除
        return $this->tagSet->rm($key);
    }

    public function clear()
    {
        // 清除该标签下所有缓存
        return $this->tagSet->clear();
    }
}
