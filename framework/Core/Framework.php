<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use Framework\Config\ConfigLoader;
use Framework\Container\Container;
use Framework\Middleware\MiddlewareDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request; // 中间件调度器
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\RouteCollection;
use think\facade\Db;

# use Framework\Annotations\AnnotationRouteLoader

class Framework
{
    // 控制器基础配置（可从配置文件读取，此处简化为常量）
    private const CONTROLLER_DIR = __DIR__ . '/../../app/Controllers';

    private const CONTROLLER_NAMESPACE = 'App\Controllers';

    private const ROUTE_CACHE_FILE = BASE_PATH . '/storage/cache/routes.php';

    // 添加数据库配置文件常量
    private const DATABASE_CONFIG_FILE = BASE_PATH . '/config/database.php';

    protected Kernel $kernel;

    private static ?Framework $instance = null;

    private Request $request; // ← 新增

    private Container $container;

    private Router $router;

    private $logger;

    private MiddlewareDispatcher $middlewareDispatcher;

    public function __construct()
    {
        // 0. 加载配置
        $configLoader = new ConfigLoader(BASE_PATH . '/config');
        $globalConfig = $configLoader->loadAll();

        // 1. 初始化DI容器（核心：后续所有依赖从这里获取）
        Container::init(); // 加载服务配置
        $this->container = Container::getInstance();

        // 示例
        // $loggers = $this->container->get(\Framework\Log\LoggerService::class);
        // $loggers->info('Container loaded successfully!');

        // ✅ 2. 自动创建并启动 Kernel（注册服务）
        // $env = $_ENV['APP_ENV'] ?? 'prod';
        // $debug = filter_var($_ENV['DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
        // $this->kernel = new Kernel($env, $debug);
        $this->kernel = new Kernel($this->container);
        $this->kernel->boot(); // <-- 容器在此时初始化，App::setContainer() 被调用

        // 3. 初始化数据库ORM
        $this->initORM();

        // 4. 初始化日志服务
        $this->logger = app('log');

        // 5. 加载所有路由（手动+注解）
        $allRoutes = $this->loadAllRoutes();

        // 6. 加载中间件调度器
        $this->middlewareDispatcher = new MiddlewareDispatcher($this->container);

        // 7. 初始化路由和中间件调度器
        $this->router = new Router(
            $allRoutes,
            $this->container,	// 或者new Container()
            self::CONTROLLER_NAMESPACE
        );
    }

    /**
     * 框架入口：完整调度流程.
     */
    public function run()
    {
        $start         = microtime(true);
        $this->request = Request::createFromGlobals();
        $request       = $this->request;

        try {
            // 1. 路由匹配
            $route = $this->router->match($request);
            if (! $route) {
                $response = $this->handleNotFound();
                $this->logger->logRequest($request, $response, microtime(true) - $start);
                $response->send();
                return;
            }

            // 彩蛋处理
            if ($route['controller'] === '__FrameworkVersionController__' && $route['method'] === '__showVersion__') {
                $response = EasterEgg::getResponse();
                $response->send();
                exit;
            }
            if ($route['controller'] === '__FrameworkTeamController__' && $route['method'] === '__showTeam__') {
                $response = EasterEgg::getTeamResponse();
                $response->send();
                exit;
            }

            // 绑定路由
            $request->attributes->set('_route', $route);

            // 执行中间件 + 控制器
            $response = $this->middlewareDispatcher->dispatch($request, function ($req) use ($route) {
                return $this->callController($route);
            });
        } catch (\Throwable $e) {
            // 🔥 记录异常（使用 Symfony Request）
            $this->logger->logException($e, $request);

            // 返回友好错误响应
            $response = $this->handleException($e);
        }

        // 统一日志记录（包括异常情况）
        $this->logger->logRequest($request, $response, microtime(true) - $start);

        $response->send();
    }

    /*
    单例模式，实例化
    */
    public static function getInstance(): Framework
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ✅ 对外提供容器
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    // 可选：提供访问容器或内核的接口
    public function getKernel(): Kernel
    {
        return $this->kernel;
    }

    // 可选： 实现Kernel的getContainer，使用别名
    public function get_Container()
    {
        return $this->kernel->getContainer();
    }

    /**
     * 初始化 ThinkORM 数据库配置.
     */
    private function initORM()
    {
        // 检查数据库配置文件是否存在
        if (! file_exists(self::DATABASE_CONFIG_FILE)) {
            throw new \Exception('Database configuration file not found: ' . self::DATABASE_CONFIG_FILE);
        }
        // 加载数据库配置
        $config = require self::DATABASE_CONFIG_FILE;
        // 验证配置格式
        if (! isset($config['connections']) || ! is_array($config['connections'])) {
            throw new \Exception('Invalid database configuration format');
        }

        // 初始化 ThinkORM
        Db::setConfig($config);
        // 可选：在开发环境下开启 SQL 监听（用于调试）
        if (defined('APP_DEBUG') && APP_DEBUG) {
            Db::listen(function ($sql, $time, $explain) {
                // 可以记录到日志或输出到控制台
                $this->logger->info("SQL: {$sql} [Time: {$time}s]");
            });
        }
    }

    private function callController(array $route): Response
    {
        $controllerClass = $route['controller'];
        $method          = $route['method'];
        $routeParams     = $route['params'] ?? [];

        // 1. 从容器获取控制器实例
        $controller = $this->container->get($controllerClass);

        // 2. 使用反射分析方法参数
        $reflection = new \ReflectionMethod($controllerClass, $method);
        $parameters = $reflection->getParameters();

        // 3. 只处理“标量/无类型”参数（跳过 Request、自定义服务等对象）
        foreach ($parameters as $param) {
            $type = $param->getType();

            // 如果是对象类型（非内置类型），交给 ArgumentResolver 自动注入，跳过
            if ($type && ! $type->isBuiltin()) {
                continue;
            }

            $paramName = $param->getName();

            // 优先：路径参数
            if (isset($routeParams[$paramName])) {
                $this->request->attributes->set($paramName, $routeParams[$paramName]);
                continue;
            }

            // 其次：查询参数（$_GET）
            if ($this->request->query->has($paramName)) {
                $this->request->attributes->set($paramName, $this->request->query->get($paramName));
                continue;
            }

            // 没有提供值？如果有默认值，ArgumentResolver 会处理；否则 PHP 会报错（符合预期）
        }

        // 4. 使用 Symfony 的 ArgumentResolver 解析所有参数（包括 Request 等）
        $argumentResolver = new ArgumentResolver();
        $arguments        = $argumentResolver->getArguments($this->request, [$controller, $method]);

        // 5. 调用控制器方法
        $response = $controller->{$method}(...$arguments);

        // 6. 确保返回 Response 对象
        if (! $response instanceof Response) {
            if (is_array($response) || is_object($response)) {
                $response = new Response(
                    json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    200,
                    ['Content-Type' => 'application/json']
                );
            } else {
                $response = new Response((string) $response);
            }
        }

        return $response;
    }

    /**
     * 加载所有路由（手动路由 + 注解路由），支持缓存.
     */
    private function loadAllRoutes(): RouteCollection
    {
        // 检查路由缓存
        if (file_exists(self::ROUTE_CACHE_FILE)) {
            $serializedRoutes = file_get_contents(self::ROUTE_CACHE_FILE);
            $routes           = unserialize($serializedRoutes);
            if ($routes instanceof RouteCollection) {
                return $routes;
            }
            // 缓存损坏，删除旧缓存
            unlink(self::ROUTE_CACHE_FILE);
        }

        // 1. 加载手动路由（从 config/routes.php 读取）
        $manualRoutes = require BASE_PATH . '/config/routes.php';
        $allRoutes    = new RouteCollection();
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

        /*
        * doctrine/annotations 注解路由，遗弃 https://packagist.org/packages/doctrine/annotations
        * composer remove doctrine/annotations
        * 移除Framework\Annotations\下面的包文件
        * 移除Framework\Annotations\AnnotationRouteLoader
        * 具体测试：TestController.php
        */

        // 2. 加载注解路由（通过 AnnotationRouterLoader）
        // $annotationLoader = new AnnotationRouteLoader(
        //    self::CONTROLLER_DIR,
        //    self::CONTROLLER_NAMESPACE
        // );
        // $annotatedRoutes = $annotationLoader->loadRoutes(); // 调用正确的方法名
        // $allRoutes->addCollection($annotatedRoutes);

        // 缓存合并后的路由
        //$this->cacheRoutes($allRoutes, self::ROUTE_CACHE_FILE);

        return $allRoutes;
    }

    /*
    404 not found
    */
    private function handleNotFound()
    {
        $responseContent = view('errors/404.html.twig', [
            'status_code' => Response::HTTP_NOT_FOUND, // 404
            'status_text' => 'Not Found',
            'message'     => '404 Page Not Found. Please refresh the page and try again.',
        ]);

        return new Response($responseContent, Response::HTTP_NOT_FOUND);
    }

    /*
    500 错误的友好页面
    */
    private function handleException(\Throwable $e)
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
     * 缓存路由集合.
     */
    private function cacheRoutes(RouteCollection $routes, string $file)
    {
        $dir = dirname($file);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, serialize($routes));
    }
}
