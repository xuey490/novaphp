<?php
// config/services.php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    // 默认配置
    $services
        ->defaults()
        ->autowire()      // 所有服务默认自动装配
        ->autoconfigure() // 所有服务默认自动配置
    ;

    // 示例服务	
    $services->set('db.connection', \PDO::class)
        ->args([
            'mysql:host=localhost;dbname=test;charset=utf8mb4',
            'root',
            'root',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        ])->public();

		
    // ✅ 1. 手动注册 PDO 服务
	/*
    $services->set('pdo', \PDO::class)
        ->factory([static function () {
            $dsn = 'mysql:host=127.0.0.1;dbname=novaphp;charset=utf8mb4';
            $user = 'root';
            $password = '';
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ];
            return new \PDO($dsn, $user, $password, $options);
        }, '__invoke']);
	*/
    // 示例：注册一个服务 如果你有 test.service 且要手动 get() 必须加public这一行
    $services->set('test.service', \stdClass::class)->public();
	
	//手动注册 2. 业务服务（private，默认）
	$services->set('Framework\Middleware\MethodOverrideMiddleware')
		->autowire()
		->autoconfigure()->public();


    $services->load('App\\Middleware\\', '../app/Middleware/**/*Middleware.php')
        ->autowire()      // 支持中间件的依赖自动注入（如注入UserService）
        ->autoconfigure() // 支持中间件添加标签（如后续需要事件监听）
        ->public(); // 关键：标记为公开，因为中间件需要通过容器动态获取（如从注解解析后）

	#$services->load('App\\', '../app/*/*')->exclude('../app/{Entity,Tests}/*') ->autowire()->autoconfigure();
	
	
    // ✅ 自动注册所有 Services（包括 UserService）
    $services->load('App\\Services\\', '../app/Services/*Service.php')
        ->autowire()
        ->autoconfigure()->public(); // 如果你后续要直接 get() 它，才需要 public；否则可省略
		

    // ✅ 自动加载控制器（关键：使用相对路径）
    // 3. 控制器（必须 public！）
    $services->load('App\\Controllers\\', '../app/Controllers/**/*Controller.php')
        ->autowire()
        ->autoconfigure()->public();
};