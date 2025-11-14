<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: CookieServiceProvider.php
 * @Date: 2025-11-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;


/*
* 注册全局的cookie 服务
*/
final class CookieServiceProvider implements ServiceProviderInterface
{
    //public function __invoke(ContainerConfigurator $configurator): void
	public function register(ContainerConfigurator $configurator): void
    {
		$services = $configurator->services();

		$cookieConfig = BASE_PATH . '/config/cookie.php';
		// 注册 Cookie 服务，并传入配置
		$services->set( \Framework\Utils\CookieManager::class)
			->args([
				$cookieConfig,
			])
			->public();
		$services->set('cookie', \Framework\Utils\CookieManager::class)
			->public();
 
			
    }
	
    public function boot(ContainerInterface $container): void
    #public function boot(ContainerConfigurator $container): void
    {

    }	
	
}
