<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: MiddlewaresProvider.php
 * @Date: 2025-11-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Reference;

final class MiddlewaresProvider implements ServiceProviderInterface
{
    //public function __invoke(ContainerConfigurator $configurator): void
	public function register(ContainerConfigurator $configurator): void
    {
		$services = $configurator->services();

		//Override
		$services->set(\Framework\Middleware\MethodOverrideMiddleware::class)
			->autowire()
			->autoconfigure()
			->public();

		//Cors
		$services->set(\Framework\Middleware\CorsMiddleware::class)
			->autowire()
			->autoconfigure()->public();
			
		//Cookie提示
		$services->set(\Framework\Middleware\CookieConsentMiddleware::class)
			->autowire()
			->autoconfigure()->public();

		//熔断器
		$services->set(\Framework\Middleware\CircuitBreakerMiddleware::class)
			->args(['%kernel.project_dir%/storage/cache'])
			->autoconfigure()
			->public(); 
		
		//IP Block
		$services->set(\Framework\Middleware\IpBlockMiddleware::class)
			->args(['%kernel.project_dir%/config/iplist.php'])
			->public();	
		
		//XSS过滤
		$services->set(\Framework\Middleware\XssFilterMiddleware::class)
			->args([
				'$enabled'     => true,
				'$allowedHtml'  => [], //['b', 'i', 'u', 'a', 'p', 'br', 'strong', 'em'], 按需调整
			])
			->autowire()
			->public();
			
		// 注册debug中间件 默认不启动
		$services->set(\Framework\Middleware\DebugMiddleware::class)
			->args([false])
			->autowire()
			->public();		
			
		// 加载中间件配置
		$middlewareConfig = require BASE_PATH . '/config/middleware.php';
		// 动态注册：Rate_Limit 中间件
		if ($middlewareConfig['rate_limit']['enabled']) {
			//限流器
			$services->set(\Framework\Middleware\RateLimitMiddleware::class)
				->args([
				$middlewareConfig['rate_limit'],
				'%kernel.project_dir%/storage/cache/'
				])
				->autoconfigure()
				->public(); 
		}

		// 动态注册：CSRF 保护中间件 use Framework\Security\CsrfTokenManager;
		// Session 必须已注册（确保你的框架已启动 session）
		$services->set(\Framework\Security\CsrfTokenManager::class)
			->args([
				new Reference('session'), // 假设你已注册 'session' 服务
				'csrf_token'
			])->public();
		
		if ($middlewareConfig['csrf_protection']['enabled']) {
			$services->set(\Framework\Middleware\CsrfProtectionMiddleware::class)
				->args([
					new Reference(\Framework\Security\CsrfTokenManager::class),
					$middlewareConfig['csrf_protection']['token_name'],
					$middlewareConfig['csrf_protection']['except'],
					$middlewareConfig['csrf_protection']['error_message'],
					$middlewareConfig['csrf_protection']['remove_after_validation'],
				])
				->public(); // 如果要在 Kernel 中使用，需 public
		}

		// 动态注册：Referer 检查中间件
		if ($middlewareConfig['referer_check']['enabled']) {
			$services->set(\Framework\Middleware\RefererCheckMiddleware::class)
				->args([
					$middlewareConfig['referer_check']['allowed_hosts'],
					$middlewareConfig['referer_check']['allowed_schemes'],
					$middlewareConfig['referer_check']['except'],
					$middlewareConfig['referer_check']['strict'],
					$middlewareConfig['referer_check']['error_message'],
				])
				->public();
		}
			
    }
	
    public function boot(ContainerInterface $container): void
    #public function boot(ContainerConfigurator $container): void
    {

    }	
	
}
