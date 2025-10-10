<?php

namespace Framework\Routing;

use FastRoute;
use Framework\Http\Request;
use Framework\Http\Response;

class Router
{
    protected $basePath;
    protected $dispatcher;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
            $routeConfig = include $this->basePath . '/config/route.php';
            foreach ($routeConfig as $method => $routes) {
                foreach ($routes as $path => $handler) {
                    $r->addRoute($method, $path, $handler);
                }
            }
        });
    }

    public function dispatch(Request $request)
    {
        $httpMethod = $request->method();
        $uri = rawurldecode($request->path());

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                return new Response('Not Found', 404);
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                return new Response('Method Not Allowed', 405);
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                return $this->callAction($handler, $vars, $request);
            default:
                return new Response('Server Error', 500);
        }
    }

    protected function callAction($handler, $params, Request $request)
    {
        [$controllerName, $method] = is_array($handler) ? $handler : explode('@', $handler);

        $controllerClass = "App\\Controller\\{$controllerName}";
        if (!class_exists($controllerClass)) {
            return new Response("Controller not found: {$controllerClass}", 500);
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, $method)) {
            return new Response("Method not found: {$method}", 500);
        }

        return call_user_func_array([$controller, $method], array_values($params));
    }
}