<?php

namespace Framework\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;




class Container implements ContainerInterface
{
    /**
     * @var ContainerBuilder|null
     * 
     * 我们现在可以确定，这里始终是一个 ContainerBuilder 实例。
     */
    private static ?ContainerBuilder $container = null;

    /**
     * 初始化容器。
     * 每次调用都会重新加载配置并编译容器。
     */
    public static function init(): void
    {
        if (self::$container !== null) {
            return;
        }

        $projectRoot = dirname(__DIR__, 2); // NovaPHP/
        $configDir   = $projectRoot . '/config';

        if (!is_dir($configDir)) {
            throw new \RuntimeException("配置目录不存在: {$configDir}");
        }

        $servicesFile = $configDir . '/services.php';
        if (!file_exists($servicesFile)) {
            throw new \RuntimeException("服务配置文件不存在: {$servicesFile}");
        }

        // 1. 创建一个全新的 ContainerBuilder
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', $projectRoot);

        // 2. 创建加载器并加载 services.php 文件
        // 这是加载 Symfony 风格 PHP 配置文件的标准方式
        $loader = new PhpFileLoader($container, new FileLocator($configDir));
        $loader->load('services.php');	
		
		//var_dump(($container->getServiceIds()));
        // 3. 编译容器
        // 这一步会解析所有依赖关系并准备好服务
        $container->compile(true);

        // 4. 将完全构建好的容器保存到静态属性中
        self::$container = $container;
    }



    /**
     * 获取容器实例 (PSR-11 兼容)。
     *
     * @return self
     */
    public static function getInstance(): self
    {
        self::init();
        return new self();
    }

    /**
     * 获取底层的 Symfony ContainerBuilder 实例。
     * 在无缓存模式下，这个方法总是有效的。
     *
     * @return ContainerBuilder
     */
    public static function getSymfonyContainer(): ContainerBuilder
    {
        self::init();
        return self::$container;
    }

    // --- PSR-11 接口实现 ---

    /**
     * {@inheritdoc}
     */
    public function has1(string $id): bool
    {
        return self::$container->has($id);
    }

	public function has(string $id): bool
	{
		self::init();
		$result = self::$container->has($id);
		//var_dump("Container::has('$id') = ", $result);
		return $result;
	}



    /**
     * {@inheritdoc}
     */
    public function get(string $id): mixed
    {
        // 直接调用底层容器的 get() 方法，它会自行处理 ServiceNotFoundException
        return self::$container->get($id);
    }
}