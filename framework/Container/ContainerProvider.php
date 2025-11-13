<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: ContainerProvider.php
 * @Date: 2025-11-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Container;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * 自动扫描并加载 Provider 类
 */
class ContainerProvider
{

    protected array $loadedProviders = [];

    /**
     * 扫描目录并注册 Provider
     */
    public function loadFromDirectory(ContainerConfigurator $configurator, string $directory, string $namespaceBase): void
    {
        if (!is_dir($directory)) {
            // 目录不存在直接跳过
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && preg_match('/Provider\.php$/i', $file->getFilename())) {
                $className = $this->resolveClassName($file->getRealPath(), $directory, $namespaceBase);

                if (!class_exists($className)) {
                    // 不再 require_once，Composer autoload 会自动加载
                    continue;
                }

                $this->registerProvider($configurator, $className);
            }
        }
    }

    /**
     * 注册单个 Provider
     */
    public function registerProvider(ContainerConfigurator $configurator, string $className): void
    {
        // 防止重复注册
        foreach ($this->loadedProviders as $p) {
            if (get_class($p) === $className) {
                return;
            }
        }

        $ref = new ReflectionClass($className);
        if (!$ref->implementsInterface(ServiceProviderInterface::class)) {
            return;
        }

        /** @var ServiceProviderInterface $provider */
        $provider = $ref->newInstance();

        // 调用 register
        if (method_exists($provider, 'register')) {
            $provider->register($configurator);
        }

        $this->loadedProviders[] = $provider;
    }

    /**
     * 启动所有 Provider 的 boot 方法
     */
    public function bootProviders(ContainerConfigurator $configurator): void
    {
        foreach ($this->loadedProviders as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot($configurator);
            }
        }
    }

    /**
     * 根据文件路径解析命名空间类名
     */
    protected function resolveClassName(string $filePath, string $baseDir, string $namespaceBase): string
    {
        // 标准化目录分隔符
        $baseDir = rtrim(str_replace('\\', '/', $baseDir), '/');
        $filePath = str_replace('\\', '/', $filePath);

        // 生成相对路径
        $relative = ltrim(str_replace($baseDir, '', $filePath), '/');
        $relative = str_replace('/', '\\', $relative);
        $relative = str_replace('.php', '', $relative);

        return trim($namespaceBase . $relative, '\\');
    }
}
