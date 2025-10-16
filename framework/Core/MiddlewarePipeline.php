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
            $carry    = function () use ($instance, $carry, $request) {
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
