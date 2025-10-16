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

namespace Framework\Attributes;

use Attribute;

/**
 * 完全兼容 Symfony 路由写法的 Attribute 路由定义类
 * ✅ 支持：path、methods、name、defaults、requirements、schemes、host
 * ✅ 扩展：prefix、group、middleware（控制器级继承）.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $path = '',
        public array $methods = [],
        public ?string $name = null,
        public array $defaults = [],
        public array $requirements = [],
        public array $schemes = [],
        public ?string $host = null,

        // ==== 扩展属性 ====
        public ?string $prefix = null,
        public ?string $group = null,
        public array $middleware = [],
    ) {}
}
