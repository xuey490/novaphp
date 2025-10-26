<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: Framework.php
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use Framework\Config\ConfigLoader;
use Framework\Container\Container;
use Framework\Middleware\MiddlewareDispatcher;
use Framework\Core\AttributeRouteLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\RouteCollection;
use think\facade\Db;

class Framework
{
    // 核心路径常量（可通过配置覆盖）
    private const CONTROLLER_DIR = BASE_PATH . '/app/Controllers';
    private const CONTROLLER_NAMESPACE = 'App\Controllers';
    private const ROUTE_CACHE_FILE = BASE_PATH . '/storage/cache/routes.php';
    private const DATABASE_CONFIG_FILE = BASE_PATH . '/config/database.php';
    private const DIR_PERMISSION = 0777; // 目录默认权限

    private static ?Framework $instance = null;
    private Request $request;
    private ContainerInterface $container;
    private Router $router;
    private MiddlewareDispatcher $middlewareDispatcher;
    private Kernel $kernel;
    private mixed $logger;

    /**
     * 单例模式：禁止外部实例化
     */
    private function __construct()
    {
        $this->initializeBasePath();
        $this->createRequiredDirs();
        $this->initializeConfigAndContainer();
        $this->initializeDependencies();
    }

    /**
     * 单例模式：获取实例
     */
    public static function getInstance(): Framework
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 框架入口：完整调度流程
     */
    public function run(): void
    {
        $start = microtime(true);
        $this->request = Request::createFromGlobals();

        try {
            // 1. 路由匹配
            $route = $this->router->match($this->request);
            if (!$route) {
                $response = $this->handleNotFound();
                $this->logRequestAndResponse($this->request, $response, $start);
                $response->send();
                return;
            }

            // 2. 彩蛋处理
            if ($this->isEasterEggRoute($route)) {
                $response = $this->handleEasterEgg($route);
                $this->logRequestAndResponse($this->request, $response, $start);
                $response->send();
                return;
            }

            // 3. 绑定路由到请求
            $this->request->attributes->set('_route', $route);
			
			// 4. 加载中间件调度器
			//$this->middlewareDispatcher = new MiddlewareDispatcher($this->container);

            // 5. 执行中间件 + 控制器
            $response = $this->middlewareDispatcher->dispatch(
                $this->request,
                fn(Request $req) => $this->callController($route)
            );
        } catch (\Throwable $e) {
            // 记录异常并返回错误响应
            $this->logger->logException($e, $this->request);
            $response = $this->handleException($e);
        }

        // 统一日志记录
        $this->logRequestAndResponse($this->request, $response, $start);
        $response->send();
    }

	/*
	* 由workerman调度 ##
	* 传入的是symfony 的request
	*/
	public function handleRequest(Request $request): Response
	{
		$start = microtime(true);
		$this->request = $request;
		try {
			$route = $this->router->match($this->request);
			
			// 未匹配路由
			if (!$route) {
				$response = $this->handleNotFound();
				$this->logRequestAndResponse($this->request, $response, $start);
				//$response->send();
				//return $;
				return $response;
			}

			// 特殊 EasterEgg 路由（如果你有）
			if ($this->isEasterEggRoute($route)) {
				return $this->handleEasterEgg($route);
			}

			// 通过中间件分发执行控制器
			$response = $this->middlewareDispatcher->dispatch(
				$this->request,
				function ($req) use ($route) {
					return $this->callController($route);
				}
			);

			// 若结果不是 Response，转换一下
			if (!$response instanceof Response) {
				$response = $this->normalizeResponse($response);
			}

			// 记录日志
			$this->logRequestAndResponse($this->request, $response, $start);

			return $response;
		} catch (\Throwable $e) {
			// 捕获异常，交给 handleException
			$this->logger->logException($e, $this->request);
			return $this->handleException($e);
		}
	}

	protected function logError(string $message): void
	{
		$logDir = BASE_PATH . '/storage/logs';
		if (!is_dir($logDir)) {
			@mkdir($logDir, 0777, true);
		}

		$file = $logDir . '/error.log';
		$time = date('Y-m-d H:i:s');
		@file_put_contents($file, "[$time] $message\n", FILE_APPEND);
	}



    /**
     * 获取容器（对外提供接口）
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 初始化 BASE_PATH
     */
    private function initializeBasePath(): void
    {
        if (!defined('BASE_PATH')) {
            // 简化路径计算：基于当前文件位置定位项目根目录
            define('BASE_PATH', realpath(dirname(__DIR__, 3)));
        }
    }

    /**
     * 创建必需目录（支持权限配置）
     */
    private function createRequiredDirs(): void
    {
        $dirs = [
            BASE_PATH . '/storage/cache',
            BASE_PATH . '/storage/logs',
            BASE_PATH . '/storage/view',
        ];

        // 从配置获取目录权限（默认 0777）
        $permission = config('app.dir_permission', self::DIR_PERMISSION);

        foreach ($dirs as $dir) {
            if (!is_dir($dir) && !mkdir($dir, $permission, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('无法创建目录: %s', $dir));
            }
        }
    }

    /**
     * 初始化配置和容器（核心流程）
     */
    private function initializeConfigAndContainer(): void
    {
        // 1. 加载配置
        //$configLoader = new ConfigLoader(BASE_PATH . '/config');
        //$globalConfig = $configLoader->loadAll();

        // 2. 初始化容器并注入配置
        Container::init();
        $this->container = Container::getInstance();

        // 3. 启动内核
        $this->kernel = new Kernel($this->container);
        $this->kernel->boot();

        // 4. 从容器获取日志服务
        $this->logger = $this->container->get('log');
		/*
        $this->logger->info('Framework initialized successfully', [
            'base_path' => BASE_PATH,
            'env' => config('app.env'),
        ]);
		*/
    }

    /**
     * 初始化所有依赖组件
     */
    private function initializeDependencies(): void
    {
        // 1. 初始化数据库ORM
        $this->initORM();

        // 2. 加载路由（支持缓存）
        $allRoutes = $this->loadAllRoutes();

        // 3. 初始化中间件调度器
        //$this->middlewareDispatcher = $this->container->get(MiddlewareDispatcher::class);
		$this->middlewareDispatcher = new MiddlewareDispatcher($this->container);

        // 4. 初始化路由
        $this->router = new Router(
            $allRoutes,
            $this->container,
            self::CONTROLLER_NAMESPACE
        );
    }

    /**
     * 加载所有路由（手动+注解，支持环境区分的缓存）
     */
    private function loadAllRoutes(): RouteCollection
    {
        $isProduction = config('app.env') === 'prod';

        // 生产环境且缓存存在时，直接加载缓存
        if ($isProduction && file_exists(self::ROUTE_CACHE_FILE)) {
            $serializedRoutes = file_get_contents(self::ROUTE_CACHE_FILE);
            $routes = unserialize($serializedRoutes);
            if ($routes instanceof RouteCollection) {
                $this->logger->info('Loaded routes from cache');
                return $routes;
            }
            $this->logger->warning('Route cache is invalid, regenerating');
            unlink(self::ROUTE_CACHE_FILE);
        }

        // 1. 加载手动路由
        $manualRoutes = require BASE_PATH . '/config/routes.php';
        $allRoutes = new RouteCollection();
        if ($manualRoutes instanceof RouteCollection) {
            $allRoutes->addCollection($manualRoutes);
        }

        // 2. 加载 Attribute 注解路由
        $attrLoader = new AttributeRouteLoader(
            self::CONTROLLER_DIR,
            self::CONTROLLER_NAMESPACE
        );
        $annotatedRoutes = $attrLoader->loadRoutes();
        $allRoutes->addCollection($annotatedRoutes);
		


        // 生产环境缓存路由
        if ($isProduction) {
            $this->cacheRoutes($allRoutes);
        }

        $this->logger->info(sprintf('Loaded %d routes (manual: %d, annotated: %d)',
            $allRoutes->count(),
            $manualRoutes->count() ?? 0,
            $annotatedRoutes->count()
        ));

        return $allRoutes;
    }

    /**
     * 缓存路由集合（添加序列化错误处理）
     */
    private function cacheRoutes(RouteCollection $routes): void
    {
        $serialized = serialize($routes);
        if ($serialized === false) {
            throw new \RuntimeException('Failed to serialize route collection');
        }

        file_put_contents(self::ROUTE_CACHE_FILE, $serialized);
        chmod(self::ROUTE_CACHE_FILE, 0644); // 缓存文件权限只读
    }

    /**
     * 初始化 ThinkORM
     */
    private function initORM(): void
    {
        if (!file_exists(self::DATABASE_CONFIG_FILE)) {
            throw new \RuntimeException('Database configuration file not found: ' . self::DATABASE_CONFIG_FILE);
        }

        $config = require self::DATABASE_CONFIG_FILE;
        if (!isset($config['connections']) || !is_array($config['connections'])) {
            throw new \RuntimeException('Invalid database configuration format');
        }

        Db::setConfig($config);

        // 开发环境开启 SQL 日志
        if (app('config')->get('app.debug')) {
            Db::listen(function ($sql, $time, $explain) {
                $this->logger->info("SQL Execution", [
                    'sql' => $sql,
                    'time' => $time . 's',
                    'explain' => $explain ?? [],
                ]);
            });
        }
    }

    /**
     * 调用控制器方法（优化参数解析和返回值处理）
     */
    private function callController(array $route): Response
    {
        $controllerClass = $route['controller'];
        $method = $route['method'];
        $routeParams = $route['params'] ?? [];

        // 从容器获取控制器实例（支持依赖注入）
        $controller = $this->container->get($controllerClass);

        // 处理路径参数和查询参数的类型转换
        $this->processRequestParameters($controllerClass, $method, $routeParams);

        // 解析控制器方法参数（Symfony ArgumentResolver）
        $argumentResolver = new ArgumentResolver();
        $arguments = $argumentResolver->getArguments($this->request, [$controller, $method]);

        // 调用控制器方法
        $response = $controller->{$method}(...$arguments);

        // 统一处理返回值
        return $this->normalizeResponse($response);
    }

    /**
     * 处理请求参数类型转换
     */
    private function processRequestParameters(string $controllerClass, string $method, array $routeParams): void
    {
        $reflection = new \ReflectionMethod($controllerClass, $method);
        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $type = $param->getType();

            // 优先获取路径参数，其次查询参数
            if (isset($routeParams[$paramName])) {
                $value = $routeParams[$paramName];
            } elseif ($this->request->query->has($paramName)) {
                $value = $this->request->query->get($paramName);
            } else {
                continue; // 无参数值，跳过
            }

            // 内置类型转换
            if ($value !== null && $type && $type->isBuiltin()) {
                $value = $this->castValueToType($value, $type->getName());
                $this->request->attributes->set($paramName, $value);
            }
        }
    }

    /**
     * 类型转换工具方法
     */
    private function castValueToType(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int' => (int)$value,
            'float' => (float)$value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string)$value,
            'array' => is_array($value) ? $value : explode(',', (string)$value),
            default => $value,
        };
    }

    /**
     * 标准化响应格式
     */
    private function normalizeResponse(mixed $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        if ($response === null) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        if (is_array($response) || is_object($response)) {
            return new Response(
                json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response((string)$response, Response::HTTP_OK);
    }

    /**
     * 处理 404 错误
     */
    private function handleNotFound(): Response
    {
        $content = view('errors/404.html.twig', [
            'status_code' => Response::HTTP_NOT_FOUND,
            'status_text' => 'Not Found',
            'message' => 'The requested page could not be found.',
            'path' => $this->request->getPathInfo(),
        ]);

        return new Response($content, Response::HTTP_NOT_FOUND);
    }


    /* 遗弃
    500 错误的友好页面
    */
    private function handleException1(\Throwable $e): Response
    {
        // 设置HTTP响应头为500
        http_response_code(500);

        // 渲染Twig模板，并将异常对象传递过去
        // 注意：我们传递的是整个$e对象，而不是print_r的结果
        $html = view('errors/500.html.twig', [
            'exception' => $e,
        ]);
        // 返回一个包含渲染后HTML的Response对象
        return new Response($html, 500);
        // return new Response('500 Server Error', 500);
    }

    /**
     * 处理异常
     */ 
    private function handleException(\Throwable $e): Response
    {
        $statusCode = $e instanceof \Framework\Core\Exception\Handler 
            ? $e->getStatusCode() 
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        // 开发环境显示详细错误，生产环境显示友好提示
		// 准备模板所需的所有变量（直接传递具体值，不依赖模板函数）
		$templateVars = [
			// 异常信息
			'exception_class' => get_class($e),
			'exception_code' => $e->getCode(),
			'exception_message' => $e->getMessage(),
			'exception_file' => $e->getFile(),
			'exception_line' => $e->getLine(),
			'trace' => $e->getTraceAsString(),
			'stack_frames' => count($e->getTrace()), // 堆栈帧数

			// 请求信息（从当前 request 对象获取）
			'request_method' => $this->request->getMethod(),
			'request_uri' => $this->request->getUri(),
			'client_ip' => $this->request->getClientIp() ?: 'unknown',
			'request_time' => date('Y-m-d H:i:s'),
			'user_agent' => $this->request->headers->get('User-Agent') ?: 'unknown',

			// 环境信息（从容器或配置获取）
			'php_version' => PHP_VERSION,
			'app_env' => config('app.env'), 
			'app_debug' => config('app.debug'), 
		];
		
		// 开发环境渲染调试模板
		if (config('app.debug')) {
			$content = view('errors/debug.html.twig', $templateVars);
		} else {
			$content = view('errors/500.html.twig', [
				'status_code' => $statusCode,
				'status_text' => Response::$statusTexts[$statusCode] ?? 'Server Error',
				'message' => 'An unexpected error occurred. Please try again later. 程序发生错误，请稍后再试！',
			]);
		}

        return new Response($content, $statusCode);
    }

    /**
     * 彩蛋路由判断
     */
    private function isEasterEggRoute(array $route): bool
    {
        return (
            ($route['controller'] === '__FrameworkVersionController__' && $route['method'] === '__showVersion__') ||
            ($route['controller'] === '__FrameworkTeamController__' && $route['method'] === '__showTeam__')
        );
    }

    /**
     * 处理彩蛋响应
     */
    private function handleEasterEgg(array $route): Response
    {
        if ($route['controller'] === '__FrameworkVersionController__') {
            return EasterEgg::getResponse();
        }
        return EasterEgg::getTeamResponse();
    }

    /**
     * 记录请求和响应日志
     */
    private function logRequestAndResponse(Request $request, Response $response, float $startTime): void
    {
        $duration = microtime(true) - $startTime;
        $this->logger->info('Request processed', [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'status' => $response->getStatusCode(),
            'duration' => round($duration * 1000, 2) . 'ms', // 转换为毫秒
            'ip' => $request->getClientIp(),
        ]);
    }

    /**
     * 防止克隆单例实例
     */
    private function __clone() {}


    /**
     * 防止反序列化单例实例（修正为 public 可见性）
     */
    public function __wakeup() 
    {
        // 反序列化时抛出异常，彻底禁止重建实例
        throw new \RuntimeException('Cannot unserialize singleton');
    }
}