<?php
// config/services.php
// 这个是个核心的配置文件，如果不懂，请参考symfony服务注册器的语法或下面的例子

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

use Valitron\Validator;


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
	
    // 加载session redis配置
    $redisConfig = require __DIR__ . '/redis.php';
    $sessionConfig = require __DIR__ . '/session.php';

    $storageType = $sessionConfig['storage_type'];
    $sessionOptions = $sessionConfig['options'];

    // === 1. 注册 Redis 客户端（仅当需要时）===
    $services->set('redis.client', \Redis::class)
        ->factory([RedisFactory::class, 'createRedisClient'])
        ->args([$redisConfig])
        ->public();

    // === 2. 注册 Session Storage（动态选择 file/redis）===
    if ($storageType === 'redis') {
        // 使用 Redis 作为 handler
        $services->set('session.handler', RedisSessionHandler::class)
            //->args([new Reference('redis.client')]);
            ->args([service('redis.client')]);

        $services->set('session.storage', NativeSessionStorage::class)
            //->args([$sessionOptions, new Reference('session.handler')])
            ->args([$sessionOptions, service('session.handler')])
            ->public();
    } else {
        // 默认：使用原生文件存储（PHP 默认）
        $services->set('session.storage', NativeSessionStorage::class)
            ->args([$sessionOptions])
            ->public();
    }

    // === 3. 注册 Session 服务 ===
    $services->set('session', Session::class)
        ->args([new Reference('session.storage')])
        ->public();

	// 注册 ConfigLoader 为服务
	$services->set('config' , \Framework\Config\ConfigLoader::class)	//$globalConfig = $this->container->get('config')->loadAll();
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
	$services->set('log', \Framework\Log\LoggerService::class)
		->autowire()	//不带args参数
		->public();
	
	// 🔹 6. 注册异常处理类
	$services->set('exception', \Framework\Core\Exception\Handler::class)
		->autowire()
		->public();	
	$services->set(\Framework\Core\Exception\Handler::class)
		->autowire()
		->public();		
			
			
		
	// 定义缓存管理器服务（单例）
	$cacheConfig = require __DIR__ . '/cache.php';
	
	/* remove thinkCache 保留代码
	//thinkCache 注册服务
	$services->set('cache', \Framework\Cache\CacheService::class)
		->args([$cacheConfig])
		->public();
	*/
	
	// symfony/cache 注册服务		
    $services->set(\Framework\Cache\CacheFactory::class)
        ->args([$cacheConfig])->public();

    // 只注册 TagAwareAdapter
    $services->set(\Symfony\Component\Cache\Adapter\TagAwareAdapter::class)
        ->factory([service(\Framework\Cache\CacheFactory::class), 'create'])->public();

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


	//Override
	$services->set(\Framework\Middleware\MiddlewareMethodOverride::class)
		->autowire()
		->autoconfigure()
		->public();

	//Cors
	$services->set(\Framework\Middleware\MiddlewareCors::class)
		->autowire()
		->autoconfigure()->public();
		

	//熔断器
	$services->set(\Framework\Middleware\MiddlewareCircuitBreaker::class)
		->args(['%kernel.project_dir%/storage/cache/'])
		->autoconfigure()
		->public(); 
	
	//IP Block
	$services->set(\Framework\Middleware\MiddlewareIpBlock::class)
		->args(['%kernel.project_dir%/config/iplist.php'])
		->public();	
	
	//XSS过滤
	$services->set(\Framework\Middleware\MiddlewareXssFilter::class)
		->args([
			'$enabled'     => true,
			'$allowedHtml'  => [], //['b', 'i', 'u', 'a', 'p', 'br', 'strong', 'em'], 按需调整
		])
		->autowire()
		->public();



    // 加载中间件配置
    $middlewareConfig = require __DIR__ . '/../config/middleware.php';

    // 动态注册：Rate_Limit 中间件
	if ($middlewareConfig['rate_limit']['enabled']) {

		//限流器
		$services->set(\Framework\Middleware\MiddlewareRateLimit::class)
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
        $services->set(\Framework\Middleware\MiddlewareCsrfProtection::class)
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
        $services->set(\Framework\Middleware\MiddlewareRefererCheck::class)
            ->args([
                $middlewareConfig['referer_check']['allowed_hosts'],
                $middlewareConfig['referer_check']['allowed_schemes'],
                $middlewareConfig['referer_check']['except'],
                $middlewareConfig['referer_check']['strict'],
                $middlewareConfig['referer_check']['error_message'],
            ])
            ->public();
    }
	

    // TWIG配置加载
	$TempConfig = require dirname(__DIR__) . '/config/view.php';
	$viewConfig = $TempConfig['Twig'];
	$services->set(\Twig\Loader\FilesystemLoader::class)->args([$viewConfig['paths']])->public();
	
	// 注册 AppTwigExtension 扩展
	$services->set(\Framework\View\AppTwigExtension::class)
		->args([
			service(\Framework\Security\CsrfTokenManager::class),
			'_token' // 👈 显式传入字段名
		])
		->public();
	
	// 注册 markdown 服务开始
	$services->set(\League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension::class)
		->public(); 
	
	// 注册 markdown Environment 
	$services->set(\League\CommonMark\Environment\Environment::class)
	->args([
		[
			// 这是传递给 Environment 构造函数的配置数组
			'html_input' => 'strip',
			'allow_unsafe_links' => false,
		]
	])->call('addExtension', [service(\League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension::class)])
	->public();    // Environment 对象需要加载核心扩展才能工作

	// 注册 MarkdownConverter 服务
	// 它依赖于上面 Environment 服务。
	$services->set(\League\CommonMark\MarkdownConverter::class)
		->args([
			service(\League\CommonMark\Environment\Environment::class),
		])
		->public();
	
	// 注册自定义 Markdown Twig 扩展
	// 它依赖于上面 MarkdownConverter 服务
	$services->set(\Framework\View\MarkdownExtension::class)
		->args([
			service(\League\CommonMark\MarkdownConverter::class), // 注入 MarkdownConverter
		])
		->public();	
	// Markdown Twig 扩展结束

	$services->set(\Twig\Environment::class) // ✅ 显式指定类
		->args([
			service(\Twig\Loader\FilesystemLoader::class),
			[
				'cache' => $viewConfig['cache_path'], // ✅ 字符串 或 false
				'debug' => $viewConfig['debug'],
				'auto_reload' => $viewConfig['debug'],
				'strict_variables' => $viewConfig['strict_variables'],
			],
		])
		->call('addExtension', [service(\Framework\View\AppTwigExtension::class) ])
		->call('addExtension', [service(\Framework\View\MarkdownExtension::class)]) // ✅ 添加新的 Markdown 扩展
		->public();

	// 别名
	$services->alias('view', \Twig\Environment::class)->public();

	$tpTemplateConfig = $TempConfig['Think'];

	// 1.注册 'thinkTemp' 服务，用下面的方法，更简单
	/*
	$services->set('thinkTemp', \think\Template::class)
		->args([$tpTemplateConfig])
		->public();	
	*/
	
	// 2.注册thinkTemp
    $parameters = $configurator->parameters();
	
	// 0.注册模板工厂类
	$services->set(\Framework\View\ThinkTemplateFactory::class)
		->args([$tpTemplateConfig])
		->public();	;

    // 1. 将 ThinkPHP 模板配置定义为一个容器参数
    // 这是一种更 Symfony 的做法，便于管理
    $parameters->set('think_template.config', $tpTemplateConfig);

    // 2. 注册 'thinkTemp' 服务
    $services->set('thinkTemp', \think\Template::class)
        // 使用 factory() 方法，并指向我们的工厂类
		//->factory(service(\Framework\View\ThinkTemplateFactory::class))
		->factory([service(\Framework\View\ThinkTemplateFactory::class), 'create'])
        // 为工厂方法注入配置参数
        ->args([
            // 使用 param() 来引用上面定义的参数
            param('think_template.config'),
        ])
        ->public(); // 允许从容器外部获取

	
    // 注册 MIME 检查器
    $services->set(\Framework\Utils\MimeTypeChecker::class)
             ->args([dirname(__DIR__) . '/config/mime_types.php'])->public();

    // 注册文件上传器，注入上传配置 + MIME 检查器
    $uploadConfig = include dirname(__DIR__) . '/config/upload.php';

    $services->set(\Framework\Utils\FileUploader::class)
             ->args([$uploadConfig, service(\Framework\Utils\MimeTypeChecker::class)])->public();	


	// 注册ThinkValidator工厂类
    $services->set(\Framework\Validation\ThinkValidatorFactory::class)
        ->public();
		
	// 注册thinkphp validate
    $services->set('validate', \think\Validate::class)
        // 使用 factory() 方法，并指向工厂类
		->factory([service(\Framework\Validation\ThinkValidatorFactory::class), 'create'])
		->public(); // 允许从容器外部获取
		

	// 注册事件分发
    $services->set(\Framework\Event\Dispatcher::class)
        ->arg('$container', service('service_container'))->public(); // ✅ 显式注入容器自身

	 
	//批量注册事件
	$services->load('App\\Listeners\\', '../app/Listeners/**/*.php')
		->autowire()      
		->autoconfigure() 
		->public(); 

	
	//批量注册路由中间件
	$services->load('App\\Middlewares\\', '../app/Middlewares/**/*Middleware.php')
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


//redis===================
/**
 * 工厂方法：创建 Redis 客户端
 */
class RedisFactory {
    public static function createRedisClient(array $config): \Redis {
        $redis = new \Redis();
        $connected = $redis->connect($config['host'], $config['port'], $config['timeout']);
        if (!$connected) {
            throw new RuntimeException('Failed to connect to Redis');
        }
        if (!empty($config['password'])) {
            $redis->auth($config['password']);
        }
        if (isset($config['database'])) {
            $redis->select($config['database']);
        }
        return $redis;
    }
}
