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

namespace Framework\Config;

class ConfigService
{
    public function __construct(
        private ConfigLoader $loader // 依赖 ConfigLoader 服务
    ) {}

    /**
     * 获取配置项（支持点语法：database.host）.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $config = $this->loader->loadAll();
        $keys   = explode('.', $key);
        $value  = $config;

        foreach ($keys as $k) {
            if (is_array($value) && array_key_exists($k, $value)) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * 获取全部配置（谨慎使用）.
     */
    public function all(): array
    {
        return $this->loader->loadAll();
    }
}
