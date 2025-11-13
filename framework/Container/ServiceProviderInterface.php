<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: ServiceProviderInterface.php
 * @Date: 2025-11-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */
 
namespace Framework\Container;

/**
 * 服务提供者接口定义
 */
#use Symfony\Component\DependencyInjection\ContainerBuilder; //或者直接用本目录的Container
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

interface ServiceProviderInterface
{
    /**
     * 在此阶段注册服务定义
     */
    public function register(ContainerConfigurator $container): void;


    /**
     * 在容器编译后执行（初始化逻辑）
     */
    public function boot(ContainerConfigurator $container): void;
}