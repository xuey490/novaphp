<?php
// framework/Core/MiddlewarePipeline.php
namespace Framework\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiddlewarePipeline
{
    private $container;
    private $middlewares = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function add($middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function send(Request $request, callable $next): Response
    {
        $stack = array_reverse($this->middlewares);

        $carry = $next;

        foreach ($stack as $middleware) {
            $instance = $this->resolveMiddleware($middleware);
            $carry = function () use ($instance, $carry, $request) {
                return $instance->handle($request, $carry);
            };
        }

        return $carry();
    }

    private function resolveMiddleware($middleware)
    {
        if (is_string($middleware)) {
            return $this->container->make($middleware);
        }
        return $middleware;
    }
}