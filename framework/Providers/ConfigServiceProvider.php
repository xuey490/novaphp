<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: ConfigServiceProvider.php
 * @Date: 2025-11-06
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class ConfigServiceProvider implements ServiceProviderInterface
{
    //public function __invoke(ContainerConfigurator $configurator): void
	public function register(ContainerConfigurator $configurator): void
    {
		$services = $configurator->services();
		// 注册 ConfigService 服务
		$services->set('config' , \Framework\Config\ConfigService::class)	//$globalConfig = $this->container->get('config')->loadAll();
			->args([
			'%kernel.project_dir%/config',
			'%kernel.project_dir%/storage/cache/config_cache.php'
			])
			->public();  //($this->container->get(ConfigService::class)->loadAll());
			
		// 注册 ConfigService 业务类
		$services->set(\Framework\Config\ConfigService::class)
			->args([
				'%kernel.project_dir%/config',
				'%kernel.project_dir%/storage/cache/config_cache.php'
				])
			->public();
    }
	
    public function boot(ContainerInterface $container): void
    #public function boot(ContainerConfigurator $container): void
    {

    }		
	
}
