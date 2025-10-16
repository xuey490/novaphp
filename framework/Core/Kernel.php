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

// 异常处理

class Kernel
{
    protected ?ContainerInterface $container = null;
    // protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 启动内核：构建并编译容器，载入异常类，并设置全局 App 容器.
     */
    public function boot(): void
    {
        date_default_timezone_set(config('app.time_zone'));
        /* 在容器编译前 注册,要在调用前进行编译 $containerBuilder->compile() */
        // 在容器构建阶段（使用 Symfony ContainerBuilder）
        /*
                // 或者用定义方式（推荐）
                $this->container->register(\Framework\Config\ConfigService::class)
                    ->setPublic(true);

                $this->container->register('exception', \Framework\Core\Exception\Handler::class)
                    ->setArguments([$this->debug])
                    ->setPublic(true)
                    ->setShared(true); // 默认就是 singleton
                */

        // ✅ 设置全局 App 容器（你的助手函数依赖它）
        App::setContainer($this->container);

        // $debug = app('config')->get('app.debug', false);
        // dump(app()->getServiceIds()); // 查看所有服务 ID

        // 设置全局异常处理器

        set_exception_handler(function (\Throwable $e) {
            $handler = app('exception');
            $handler->report($e);
            $handler->render($e);
        });

        // 捕获 PHP 错误（如 notice, warning）
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // 捕获致命错误（PHP 7+）
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $e = new \ErrorException(
                    $error['message'] ?? 'Fatal error',
                    0,
                    $error['type'] ?? E_ERROR,
                    $error['file'] ?? 'unknown',
                    $error['line'] ?? 0
                );
                $handler = app('exception');
                $handler->report($e);
                $handler->render($e);
            }
        });
    }

    /**
     * 获取服务容器.
     */
    public function getContainer(): ContainerBuilder
    {
        if ($this->container === null) {
            $this->boot();
        }

        return $this->container;
    }
}
