<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Cache;

/*
Cache Abstract adapt for thinkcache
*/

abstract class AbstractCache implements CacheInterface
{
    protected int $defaultTtl = 3600;	// 默认 1 小时
    protected string $prefix = '';

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    protected function formatKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
