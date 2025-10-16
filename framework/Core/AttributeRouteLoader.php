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

use Framework\Attributes\Route;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

/**
 * AttributeRouteLoader：
 * 🔹 扫描控制器目录，解析 #[Route] 注解
 * 🔹 完全兼容 Symfony Route 写法
 * 🔹 支持控制器级 prefix / middleware / group 继承
 */
class AttributeRouteLoader
{
    private string $controllerDir;

    private string $controllerNamespace;

    public function __construct(string $controllerDir, string $controllerNamespace)
    {
        $this->controllerDir       = rtrim($controllerDir, '/');
        $this->controllerNamespace = rtrim($controllerNamespace, '\\');
    }

    /**
     * 扫描控制器目录并加载所有注解路由.
     */
    public function loadRoutes(): RouteCollection
    {
        $routeCollection = new RouteCollection();

        $controllerFiles = $this->scanDirectory($this->controllerDir);

        foreach ($controllerFiles as $file) {
            $className = $this->convertFileToClass($file);
            if (! class_exists($className)) {
                continue;
            }

            $refClass = new \ReflectionClass($className);
            if ($refClass->isAbstract()) {
                continue;
            }

            // === 类级注解 ===
            $classAttrs      = $refClass->getAttributes(Route::class);
            $classPrefix     = '';
            $classGroup      = null;
            $classMiddleware = [];

            if ($classAttrs) {
                $classRoute      = $classAttrs[0]->newInstance();
                $classPrefix     = $classRoute->prefix     ?? '';
                $classGroup      = $classRoute->group      ?? null;
                $classMiddleware = $classRoute->middleware ?? [];
            }

            // === 方法级注解 ===
            foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $methodAttrs = $method->getAttributes(Route::class);

                if (empty($methodAttrs)) {
                    // 自动生成默认路由：/demo/list
                    $autoPath = '/' . strtolower(str_replace('Controller', '', $refClass->getShortName()))
                        . '/' . $method->getName();

                    $route = new SymfonyRoute(
                        $autoPath,
                        defaults: [
                            '_controller' => "{$className}::{$method->getName()}",
                            '_group'      => $classGroup,
                            '_middleware' => $classMiddleware,
                        ],
                        methods: ['GET']
                    );

                    $autoName = strtolower(str_replace('\\', '_', $className)) . '_' . $method->getName();
                    $routeCollection->add($autoName, $route);
                    continue;
                }

                foreach ($methodAttrs as $attr) {
                    $routeAttr = $attr->newInstance();

                    // ==== 合并路径 ====
                    $prefix    = trim($classPrefix, '/');
                    $path      = trim($routeAttr->path ?? '', '/');
                    $finalPath = '/' . trim($prefix . '/' . $path, '/');

                    // ==== 合并中间件并去重 ====
                    $mergedMiddleware = array_unique(array_merge(
                        (array) $classMiddleware,
                        (array) $routeAttr->middleware
                    ));

                    // ==== 创建 Symfony 路由 ====
                    $sfRoute = new SymfonyRoute(
                        path: $finalPath,
                        defaults: array_merge(
                            $routeAttr->defaults,
                            [
                                '_controller' => "{$className}::{$method->getName()}",
                                '_group'      => $routeAttr->group ?? $classGroup,
                                '_middleware' => $mergedMiddleware,
                            ]
                        ),
                        requirements: $routeAttr->requirements,
                        options: [],
                        host: $routeAttr->host ?? '',
                        schemes: $routeAttr->schemes,
                        methods: $routeAttr->methods ?: ['GET']
                    );

                    // ==== 路由命名 ====
                    $name = $routeAttr->name
                        ?? strtolower(str_replace('\\', '_', $className)) . '_' . $method->getName();

                    $routeCollection->add($name, $sfRoute);
                }
            }
        }

        // $this->loaded = true;
        return $routeCollection;
    }

    /**
     * 从类或方法中提取 Route Attribute.
     */
    private function getRouteAttribute(\Reflector $ref): ?RouteAttribute
    {
        $attributes = $ref->getAttributes(RouteAttribute::class);
        return $attributes ? $attributes[0]->newInstance() : null;
    }

    /**
     * 扫描控制器目录，返回所有PHP文件.
     */
    private function scanDirectory(string $dir): array
    {
        $rii   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }
            if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'php') {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    /**
     * 将文件路径转换为完整类名
     * 例：app/Controllers/Api/UserController.php → App\Controllers\Api\UserController.
     */
    private function convertFileToClass(string $file): string
    {
        $relative = str_replace($this->controllerDir, '', $file);
        $relative = trim(str_replace(['/', '.php'], ['\\', ''], $relative), '\\');
        return "{$this->controllerNamespace}\\{$relative}";
    }

    /**
     * 拼接控制器级别 prefix 与方法级别 path.
     */
    private function joinPath(string $prefix, string $path): string
    {
        $prefix = rtrim($prefix ?? '', '/');
        $path   = '/' . ltrim($path ?? '', '/');
        return $prefix . $path;
    }

    /**
     * 自动生成路由名称.
     */
    private function generateRouteName(string $class, string $method): string
    {
        $class = str_replace([$this->controllerNamespace . '\\', '\Controller'], '', $class);
        $class = strtolower(str_replace('\\', '.', $class));
        return "{$class}.{$method}";
    }
}
