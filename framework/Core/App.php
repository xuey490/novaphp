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

namespace Framework\Core;

use Symfony\Component\DependencyInjection\ContainerInterface;

class App
{
    protected static ?ContainerInterface $container = null;

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            throw new \RuntimeException('应用容器尚未初始化，请先调用 App::setContainer().');
        }
        return self::$container;
    }

    public static function make(string $id): object
    {
        if (self::$container === null) {
            throw new \RuntimeException('服务容器未初始化。请确保在调用 app() 前已启动应用并设置容器。');
        }
        return self::$container->get($id);
    }

    public static function has(string $id): bool
    {
        return self::$container !== null && self::$container->has($id);
    }
}
