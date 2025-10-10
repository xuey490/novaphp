<?php

// framework/Core/Kernel.php
/*
 * 纯服务容器构建器
*/

namespace Framework\Core;

use Throwable;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Framework\Core\Exception\Handler; //异常处理
use Symfony\Component\DependencyInjection\Reference;



class Kernel
{
    protected string $environment;
    protected bool $debug;
    protected ?ContainerBuilder $container = null;

    public function __construct(string $environment = 'prod', bool $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;

        if ($debug) {
            ini_set('display_errors', '1');
            error_reporting(-1);
        } else {
            ini_set('display_errors', '0');
        }
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * 启动内核：构建并编译容器，载入异常类，并设置全局 App 容器
     */
    public function boot(): void
    {

        $this->buildContainer();

        /*在容器编译前 注册,要在调用前进行编译 $containerBuilder->compile()*/
        // 在容器构建阶段（使用 Symfony ContainerBuilder）
        /*
        // 或者用定义方式（推荐）
        $this->container->register(\Framework\Config\ConfigService::class)
            ->setPublic(true);

        $this->container->register('exception.handler', \Framework\Core\Exception\Handler::class)
            ->setArguments([$this->debug])
            ->setPublic(true)
            ->setShared(true); // 默认就是 singleton
        */

        $this->container->compile();
        // ✅ 设置全局 App 容器（你的助手函数依赖它）
        App::setContainer($this->container);

        //$debug = app('config.loader')->get('app.debug', false);
        //dump(app()->getServiceIds()); // 查看所有服务 ID

        // 设置全局异常处理器
        set_exception_handler(function (\Throwable $e) {
            $handler = app('exception.handler');
            $handler->report($e);
            $handler->render($e);
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
                $handler = app('exception.handler');
                $handler->report($e);
                $handler->render($e);
            }
        });
    }

    /**
     * 获取服务容器
     */
    public function getContainer(): ContainerBuilder
    {
        if (null === $this->container) {
            $this->boot();
        }

        return $this->container;
    }

    /**
     * 构建服务容器
     */
    protected function buildContainer(): void
    {
		$request = Request::createFromGlobals();
		$requestStack = new RequestStack();
		$requestStack->push($request); // 👈 关键！

		//初始化容器构造类
        $container = new ContainerBuilder();

        // 注册 RequestStack 到容器（关键！）
		$container->set(RequestStack::class, $requestStack);
		// 或者用字符串别名（如果你在 services.php 中用 'request_stack'）
		$container->set('request_stack', $requestStack);

        // 设置内核参数（必须，因为你的 services.php 用到了 %kernel.project_dir%）
        $container->setParameter('kernel.environment', $this->environment);
        $container->setParameter('kernel.debug', $this->debug);
        $container->setParameter('kernel.project_dir', $this->getProjectDir());

        // ✅ 使用 PhpFileLoader 加载你的 services.php（支持 Configurator DSL）
        $loader = new PhpFileLoader($container, new FileLocator($this->getConfigDir()));
        $loader->load('services.php'); // <-- 自动识别并执行你的闭包

        $this->container = $container;

        // 添加资源用于缓存
        $container->addResource(new \Symfony\Component\Config\Resource\FileResource(
            $this->getConfigDir() . '/services.php'
        ));
    }

    /**
     * 获取项目根目录
     */
    public function getProjectDir(): string
    {
        return dirname(__DIR__, 2); // 从 framework/Core 到项目根
    }

    /**
     * 获取配置目录
     */
    public function getConfigDir(): string
    {
        return $this->getProjectDir() . '/config';
    }

    /**
     * 获取缓存目录（可扩展）
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/storage/cache/' . $this->environment;
    }

    /**
     * 获取日志目录（可扩展）
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/storage/logs';
    }
}
