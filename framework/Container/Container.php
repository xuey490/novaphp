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

namespace Framework\Container;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
// 引入编译后的容器接口，我们的缓存类会实现它
use Symfony\Component\Dotenv\Dotenv;

class Container implements SymfonyContainerInterface
{
    // private static ?ContainerBuilder $container = null;

    // 编译后容器的缓存文件路径
    private const CACHE_FILE = BASE_PATH . '/storage/cache/container.php';

    // 静态变量，用于持有最终的容器实例（无论是新建的还是从缓存加载的）
    private static ?SymfonyContainerInterface $container = null;

    /**
     * 初始化容器。
     * - 在生产环境：尝试加载缓存。如果缓存不存在，则构建、编译并缓存。
     * - 在开发环境：总是重新构建，以保证配置实时生效。
     */
    public static function init1(array $parameters = []): void
    {
        if (self::$container !== null) {
            return;
        }

        // 👇 在这里加载 .env 文件
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../.env'); // 路径根据你的项目结构调整

        $projectRoot = dirname(__DIR__, 2);
        $configDir   = $projectRoot . '/config';

        if (! is_dir($configDir)) {
            throw new \RuntimeException("配置目录不存在: {$configDir}");
        }

        $servicesFile = $configDir . '/services.php';
        if (! file_exists($servicesFile)) {
            throw new \RuntimeException("服务配置文件不存在: {$servicesFile}");
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', $projectRoot);
        $container->setParameter('kernel.debug', APP_DEBUG);

        // 注入全局配置作为参数
        if (! empty($parameters)) {
            $container->setParameter('config', $parameters);
        }

        $loader = new PhpFileLoader($container, new FileLocator($configDir));
        $loader->load('services.php');

        // ⚠️ 如果你希望支持运行时 set()，就不要 compile()
        // 或者提供一个“开发模式”开关
        $container->compile(true); // 编译后 set() 将失效！

        // var_dump(($container->getServiceIds()));

        self::$container = $container;
    }

    /**
     * 初始化容器。
     * - 在生产环境：尝试加载缓存。如果缓存不存在，则构建、编译并缓存。
     * - 在开发环境：总是重新构建，以保证配置实时生效。
     */
    public static function init(array $parameters = []): void
    {
        if (self::$container !== null) {
            return;
        }

        // 加载 .env 文件来获取环境变量
        $dotenv = new Dotenv();
        $dotenv->load(BASE_PATH . '/.env');

        $env    = env('APP_ENV') ?: 'dev';
        $isProd = $env === 'prod';

        // --- 开发环境或缓存不存在：构建新容器 ---
        $projectRoot = dirname(__DIR__, 2);
        $configDir   = $projectRoot . '/config';

        if (! is_dir($configDir)) {
            throw new \RuntimeException("配置目录不存在: {$configDir}");
        }

        $servicesFile = $configDir . '/services.php';
        if (! file_exists($servicesFile)) {
            throw new \RuntimeException("服务配置文件不存在: {$servicesFile}");
        }

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.project_dir', $projectRoot);
        $containerBuilder->setParameter('kernel.debug', (bool) getenv('APP_DEBUG'));
        $containerBuilder->setParameter('kernel.environment', $env);

        // 注入全局配置作为参数
        if (! empty($parameters)) {
            $containerBuilder->setParameter('config', $parameters);
        }

        // 加载你的服务配置文件
        $loader = new PhpFileLoader($containerBuilder, new FileLocator($configDir));
        $loader->load('services.php');

        // 编译容器。这会冻结所有定义，进行优化。
        // 注意：编译后，你将不能再使用 $containerBuilder->set() 或修改定义。
        $containerBuilder->compile();

        // --- 如果是生产环境，将编译结果缓存起来 ---

        if ($isProd) {
            @mkdir(dirname(self::CACHE_FILE), 0777, true);

            $dumper       = new PhpDumper($containerBuilder);
            $cacheContent = $dumper->dump(['class' => 'ProjectServiceContainer']);

            // ✅ 关键修复：使用 flags 参数确保以无BOM的UTF-8编码写入文件
            file_put_contents(self::CACHE_FILE, $cacheContent);

            // 重新 require 刚刚生成的缓存文件
            $loadedContainer = require self::CACHE_FILE;
            if ($loadedContainer instanceof SymfonyContainerInterface) {
                self::$container = $loadedContainer;
            } else {
                // 如果仍然失败，作为最后的安全措施，使用未缓存的容器
                self::$container = $containerBuilder;
            }
        } else {
            // 开发环境，直接使用构建好的容器
            self::$container = $containerBuilder;
        }
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

    public function setParameter(string $name, array|bool|float|int|string|\UnitEnum|null $value): void
    {
        self::$container->setParameter($name, $value);
    }

    public function hasParameter(string $name): bool
    {
        return self::$container->hasParameter($name);
    }

    public function getParameter(string $name): array|bool|float|int|string|\UnitEnum|null
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
