<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: LoggerServiceProvider.php
 * @Date: 2025-11-06
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class LoggerServiceProvider implements ServiceProviderInterface
{
    //public function __invoke(ContainerConfigurator $configurator): void
	public function register(ContainerConfigurator $configurator): void
    {
		$services = $configurator->services();
		// 注册 log 服务
		$services->set('log', \Framework\Log\LoggerService::class)
			->autowire()	//不带args参数
			->public();
			
		$services->set('log_cache', \Framework\Log\LoggerCache::class)
			->autowire()
			->public();	
			
    }
	
    public function boot(ContainerInterface $container): void
    #public function boot(ContainerConfigurator $container): void
    {

    }	
	
}
