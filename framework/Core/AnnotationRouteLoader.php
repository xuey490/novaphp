<?php

namespace Framework\Core;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Framework\Annotations\Route;

class AnnotationRouteLoader
{
    /**
     * 控制器根目录
     * @var string
     */
    private $controllerDir;

    /**
     * 控制器基础命名空间
     * @var string
     */
    private $controllerNamespace;

    /**
     * 注解阅读器
     * @var AnnotationReader
     */
    private $annotationReader;

    public function __construct(string $controllerDir, string $controllerNamespace)
    {
        $this->controllerDir = $controllerDir;
        $this->controllerNamespace = $controllerNamespace;

        // 初始化注解阅读器
        $this->initAnnotationReader();
    }

    /**
     * 初始化注解阅读器
     */
    private function initAnnotationReader()
    {
        // 注册注解自动加载（Doctrine Annotations 2.0+ 需手动注册）
        //AnnotationRegistry::registerLoader('class_exists');

        // 创建注解阅读器实例
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * 路由加载方法（对外兼容接口，实际调用 loadRoutes()）
     * @return RouteCollection
     */
    public function load(): RouteCollection
    {
        // 直接调用已实现的 loadRoutes() 方法
        return $this->loadRoutes();
    }

    /**
     * 加载所有注解路由
     * @return RouteCollection
     */
    public function loadRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        // 1. 查找所有控制器文件
        $finder = new Finder();
        $finder->files()
            ->in($this->controllerDir)
            ->name('*Controller.php')
            ->depth('<= 5'); // 限制目录深度，避免性能问题

        // 2. 遍历控制器文件，解析注解
        foreach ($finder as $file) {
            // 计算类名（基于文件路径）
            $className = $this->getClassNameFromFile($file);

            if (!class_exists($className)) {
                continue;
            }

            // 解析控制器级别的路由注解（用于前缀）
            $classAnnotation = $this->annotationReader->getClassAnnotation(
                new \ReflectionClass($className),
                Route::class
            );

            // 解析方法级别的路由注解
            $this->parseMethodAnnotations($className, $classAnnotation, $routes);
        }

        return $routes;
    }

    /**
     * 从文件路径计算完整类名
     */
    private function getClassNameFromFile(\SplFileInfo $file): string
    {
        // 计算相对路径（相对于控制器根目录）
        $relativePath = $file->getRelativePathname();

        // 将路径转换为命名空间格式（例：Admin/UserController.php → Admin\UserController）
        $className = str_replace(
            [DIRECTORY_SEPARATOR, '.php'],
            ['\\', ''],
            $relativePath
        );

        // 拼接完整命名空间
        return $this->controllerNamespace . '\\' . $className;
    }

    /**
     * 解析方法级别的路由注解
     */
    private function parseMethodAnnotations(
        string $className,
        ?Route $classAnnotation,
        RouteCollection $routes
    ) {
        $reflectionClass = new \ReflectionClass($className);

        // 遍历所有公共方法
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // 跳过魔术方法
            if (in_array($method->getName(), ['__construct', '__destruct', '__call'])) {
                continue;
            }

            // 获取方法上的所有路由注解（Route/Get/Post/Put/Delete）
            $methodAnnotations = $this->getMethodRouteAnnotations($method);

            if (empty($methodAnnotations)) {
                continue;
            }

            // 处理每个路由注解
            foreach ($methodAnnotations as $annotation) {
                $this->processRouteAnnotation(
                    $className,
                    $method->getName(),
                    $classAnnotation,
                    $annotation,
                    $routes
                );
            }
        }
    }

    /**
     * 获取方法上的所有路由相关注解
     */
    private function getMethodRouteAnnotations(\ReflectionMethod $method): array
    {
        $annotations = [];
        $annotationClasses = [
            Route::class,
            Framework\Annotations\Get::class,
            Framework\Annotations\Post::class,
            Framework\Annotations\Put::class,
            Framework\Annotations\Delete::class,
        ];

        foreach ($annotationClasses as $annoClass) {
            $anno = $this->annotationReader->getMethodAnnotation($method, $annoClass);
            if ($anno) {
                $annotations[] = $anno;
            }
        }

        return $annotations;
    }

    /**
     * 处理单个路由注解，生成Symfony Route对象
     */
    private function processRouteAnnotation(
        string $className,
        string $methodName,
        ?Route $classAnnotation,
        Route $methodAnnotation,
        RouteCollection $routes
    ) {
        // 1. 处理路由路径（拼接控制器前缀）
        //$classPath = $classAnnotation ? trim($classAnnotation->path, '/') : '';
        //$methodPath = trim($methodAnnotation->path, '/');

        // 使用 ?? '' 确保即使 ->path 是 null，也会被转换为空字符串
        $classPath = $classAnnotation ? trim($classAnnotation->path ?? '', '/') : '';
        $methodPath = trim($methodAnnotation->path ?? '', '/');

        // 拼接完整路径（例：控制器前缀/admin + 方法路径/user → /admin/user）
        $fullPath = '/' . implode('/', array_filter([$classPath, $methodPath]));

        // 关键步骤：为非根路径的路由自动添加.html后缀（可根据需求控制）
        // 规则：仅前台路由加.html，管理后台路由（/admin/开头）不加
        if (!empty($fullPath) && !str_starts_with($fullPath, '/admin')) {
            //$fullPath .= '.html'; // 生成如"/user/show.html"的路径
        }

        // 2. 处理请求方法
        $methods = (array)$methodAnnotation->methods;
        $methods = array_map('strtoupper', $methods);

        // 3. 处理默认参数（合并控制器和方法的默认值）
        $classDefaults = $classAnnotation ? $classAnnotation->defaults : [];
        $methodDefaults = $methodAnnotation->defaults;
        $defaults = array_merge($classDefaults, $methodDefaults);

        // 4. 处理参数约束（合并控制器和方法的约束）
        $classRequirements = $classAnnotation ? $classAnnotation->requirements : [];
        $methodRequirements = $methodAnnotation->requirements;
        $requirements = array_merge($classRequirements, $methodRequirements);

        // 5. 生成路由名称（默认格式：控制器名.方法名）
        $routeName = $methodAnnotation->name ?:
            strtolower(str_replace('\\', '.', $className) . '.' . $methodName);

        // 6. 关键：合并控制器和方法的 options（新增这部分）
        $classOptions = $classAnnotation ? $classAnnotation->options : [];
        $methodOptions = $methodAnnotation->options;
        $options = array_merge($classOptions, $methodOptions); // 类级别options会被方法级别覆盖

        // ✅ 正确方式：从 annotations 提取中间件并合并，放入 defaults
        #$classMiddleware = (array)($classAnnotation->options['_middleware'] ?? []);
        #$methodMiddleware = (array)($methodAnnotation->options['_middleware'] ?? []);
        #$finalMiddleware = array_values(array_unique(array_merge($classMiddleware, $methodMiddleware)));

        //$defaults['_middleware'] = $finalMiddleware;
        //print_r($defaults);

        // 7. 创建Symfony Route对象（控制器格式：类名::方法名）
        $controller = $className . '::' . $methodName;
        $symfonyRoute = new SymfonyRoute(
            $fullPath,	// 1. 路由路径
            array_merge($defaults, ['_controller' => $controller]),	// 2. 默认参数（含控制器）
            $requirements,	// 3. 参数约束
            $options,		// 4. 路由选项（中间件在这里！）
            '',				// 5. 主机
            [],				// 6.  schemes（http/https）
            $methods		// 7. 请求方法（GET/POST等）
        );

        // 7. 将路由添加到集合（路由名称作为键）
        //$routes->add($annotation->name ?: $methodName, $symfonyRoute);
        $routes->add($routeName, $symfonyRoute);
    }




    /**
     * 合并多个数组中的 _middleware 值，过滤空数组，去重
     *
     * @param array $data 多个数组组成的数组
     * @return array 合并后的 ['_middleware' => [...]]
     */
    private function mergeMiddleware(array $data): array
    {
        $middlewares = [];

        foreach ($data as $item) {
            // 跳过空数组或非数组元素
            if (!is_array($item) || empty($item)) {
                continue;
            }

            if (isset($item['_middleware'])) {
                $mw = $item['_middleware'];
                if (is_array($mw)) {
                    $middlewares = array_merge($middlewares, $mw);
                } else {
                    $middlewares[] = $mw;
                }
            }
        }

        // 去重并重置索引
        $middlewares = array_values(array_unique($middlewares));

        return ['_middleware' => $middlewares];
    }

}
