<?php

namespace Framework\Routing;

use Framework\Http\Request;
use Framework\Http\Response;

class Router
{
    protected $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function dispatch(Request $request)
    {
        $uri = trim($request->path(), '/');
        $method = $request->method();

        // 解析 URI 为 controller 和 action
        $parts = explode('/', $uri, 2);
        $controllerName = ucfirst($parts[0] ?: 'Home'); // 默认 HomeController
        $action = $parts[1] ?? null;

        // 构建类名
        $controllerClass = "App\\Controller\\{$controllerName}Controller";

        if (!class_exists($controllerClass)) {
            return new Response('Not Found: Controller does not exist.', 404);
        }

        // 自动推断 action
        if (!$action) {
            // 根据 HTTP 方法选择默认方法
            $action = $this->guessActionFromMethod($method);
        }

        $action = $this->toCamelCase($action); // 转驼峰（如 user-profile → userProfile）

        if (!method_exists($controllerClass, $action)) {
            return new Response("Method {$action} not found in {$controllerClass}.", 404);
        }

        // 实例化控制器并调用
        $controller = new $controllerClass();
        return $controller->$action($request);
    }

    protected function guessActionFromMethod($method)
    {
        return match ($method) {
            'GET' => 'index',
            'POST' => 'store',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'destroy',
            default => 'index'
        };
    }

    protected function toCamelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
    }
}