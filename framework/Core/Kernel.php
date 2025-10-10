<?php
// framework/Core/Kernel.php

namespace Framework\Core;

use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\FileLocator;

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
     * 启动内核：构建并编译容器，并设置全局 App 容器
     */
    public function boot(): void
    {
        $this->buildContainer();
        $this->container->compile();

        // ✅ 设置全局 App 容器（你的助手函数依赖它）
        App::setContainer($this->container);
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
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', $this->environment);
        $container->setParameter('kernel.debug', $this->debug);
        $container->setParameter('kernel.project_dir', $this->getProjectDir());

        // 加载配置文件
        $loader = new YamlFileLoader($container, new FileLocator($this->getConfigDir()));
        $loader->load('services.php');

        // 可选：自动配置
        $container->registerForAutoconfiguration(\App\Service\Attribute\AutoWired::class)
                  ->addTag('autowired');

        $this->container = $container;

        // 添加配置文件为资源（用于缓存或热重载）
        $container->addResource(new FileResource($this->getConfigDir() . '/services.php'));
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