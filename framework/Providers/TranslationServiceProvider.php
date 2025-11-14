<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: TranslationServiceProvider.php
 * @Date: 2025-11-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\HttpFoundation\RequestStack;

/*
* 注册 Translator 服务
*/
final class TranslationServiceProvider implements ServiceProviderInterface
{
    //public function __invoke(ContainerConfigurator $configurator): void
	public function register(ContainerConfigurator $configurator): void
    {
		$services = $configurator->services();

		// 多国语言翻译
		// 注册 Translator 服务（不设 locale，延迟设置）
		$services->set('translator', \Framework\Translation\TranslationService::class)
			->args([
				service(RequestStack::class), // 或 RequestStack::class
				'%kernel.project_dir%/resource/translations'
			])
		->public();
			
    }
	
    public function boot(ContainerInterface $container): void
    #public function boot(ContainerConfigurator $container): void
    {

    }		
	
}
