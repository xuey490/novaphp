<?php
// config/services.php
// 这个是个核心的配置文件，如果不懂，请参考symfony服务注册器的语法或下面的例子

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

//i18n 多国语言翻译

//use Framework\Translation\TranslatorFactory;
use Framework\Translation\TransHelper;




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
    $services->set('test', \stdClass::class)->public();
	
	// 注册 ConfigLoader 为服务
	$services->set('config.loader' , \Framework\Config\ConfigLoader::class)	//$globalConfig = $this->container->get('config')->loadAll();
		->args(['%kernel.project_dir%/config'])
		->public(); // 如果你需要 $container->get(ConfigLoader::class) //print_r($this->container->get(ConfigLoader::class)->loadAll());
		
    // 🔹 1. 注册 ConfigLoader 业务类
    $services->set(\Framework\Config\ConfigLoader::class)
        ->args(['%kernel.project_dir%/config'])
        ->public();
		
	
    // 🔹 2. 注册 ConfigService 服务类
    $services->set(\Framework\Config\ConfigService::class)
        ->public(); // 自动注入 ConfigLoader（autowire 默认开启）
		
    // 🔹 3. 注册 LoggerService 服务类
    $services->set(\Framework\Log\LoggerService::class)
		->autowire() // 自动注入 ConfigService
        ->public(); // 允许直接 $container->get()

	
    // 🔹 4. 注册 Logger 业务类
    $services->set(\Framework\Log\Logger::class)
		->args([
			'app', // channel 名称
			'%kernel.project_dir%/storage/logs/app.log' // 日志文件路径（可被 ConfigService 替代）
		])
        ->public(); // 允许直接 $container->get()
		
	// 🔹 5. 别名注册
	$services->set('log.logger', \Framework\Log\LoggerService::class)
		->autowire()	//不带args参数
		->public();
	
	// 注册异常处理类
	$services->set('exception.handler', \Framework\Core\Exception\Handler::class)
		->autowire()
		->public();	
		
		
	// 定义缓存管理器服务（单例）
	$cacheConfig = require __DIR__ . '/cache.php';

	$services->set('cache.manager', \Framework\Cache\CacheService::class)
		->args([$cacheConfig])
		->public();

    // 注册 RequestStack（用于在工厂中获取当前请求）
    $services->set(RequestStack::class);


	// i18n 多国语言翻译
    // 注册 Translator 服务（不设 locale，延迟设置）
	$services->set('translator1', \Framework\Translation\TranslationService::class)
		->args([
			service(RequestStack::class), // 或 RequestStack::class
			'%kernel.project_dir%/resource/translations'
		])
		->public();
		


    // 注册翻译助手，传入依赖
    $services->set('translator', \Framework\Translation\TransHelper::class)
        ->args([
            service(RequestStack::class),
            '%kernel.project_dir%/resource/translations',
        ])->public();

	

	

		
		/*
		$services->set('config', \Framework\Config\ConfigService::class)
			->autowire()
			->public();
		*/
		
	/*使用
			Container::init(); // 加载服务配置
			$this->container = Container::getInstance();
			//$config = $this->container->get(\Framework\Config\ConfigService::class);
			//$dbHost = $config->get('database.host');
			//print_r($config->all());
	*/	
		
	
	
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