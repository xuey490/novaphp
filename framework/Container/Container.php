<?php

namespace Framework\Container;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use UnitEnum; // 👈 必须引入


class Container implements SymfonyContainerInterface
{
    private static ?ContainerBuilder $container = null;

    /**
     * 初始化容器，可选传入配置参数
     */
    public static function init(array $parameters = []): void
    {
        if (self::$container !== null) {
            return;
        }

        $projectRoot = dirname(__DIR__, 2);
        $configDir   = $projectRoot . '/config';

        if (!is_dir($configDir)) {
            throw new \RuntimeException("配置目录不存在: {$configDir}");
        }

        $servicesFile = $configDir . '/services.php';
        if (!file_exists($servicesFile)) {
            throw new \RuntimeException("服务配置文件不存在: {$servicesFile}");
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', $projectRoot);

        // 注入全局配置作为参数
        if (!empty($parameters)) {
            $container->setParameter('config', $parameters);
        }

        $loader = new PhpFileLoader($container, new FileLocator($configDir));
        $loader->load('services.php');

        // ⚠️ 如果你希望支持运行时 set()，就不要 compile()
        // 或者提供一个“开发模式”开关
        $container->compile(true); // 编译后 set() 将失效！
		
		//var_dump(($container->getServiceIds()));

        self::$container = $container;
    }

    public static function getInstance(): self
    {
        self::init();
        return new self();
    }

    // ========== 代理所有 Symfony ContainerInterface 方法 ==========

	public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
	{
		return self::$container->get($id, $invalidBehavior);
	}

    public function has(string $id): bool
    {
        return self::$container->has($id);
    }

    public function set(string $id, mixed $service): void
    {
        // ⚠️ 注意：编译后的容器会抛出异常！
        self::$container->set($id, $service);
    }

    public function initialized(string $id): bool
    {
        return self::$container->initialized($id);
    }

    public function getServiceIds(): array
    {
        return self::$container->getServiceIds();
    }

    public function setParameter(string $name, UnitEnum|array|string|int|float|bool|null $value): void
    {
        self::$container->setParameter($name, $value);
    }

    public function hasParameter(string $name): bool
    {
        return self::$container->hasParameter($name);
    }

    public function getParameter(string $name): UnitEnum|array|string|int|float|bool|null
    {
        return self::$container->getParameter($name);
    }

    public function getParameterBag()
    {
        return self::$container->getParameterBag();
    }

    public function compile(bool $resolveEnvPlaceholders = false): void
    {
        self::$container->compile($resolveEnvPlaceholders);
    }

    public function isCompiled(): bool
    {
        return self::$container->isCompiled();
    }

    public function getCompilerPassConfig()
    {
        return self::$container->getCompilerPassConfig();
    }

    public function addCompilerPass($pass, string $type = 'beforeOptimization', int $priority = 0): static
    {
        self::$container->addCompilerPass($pass, $type, $priority);
        return $this;
    }
}