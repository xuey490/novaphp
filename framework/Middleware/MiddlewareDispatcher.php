<?php
namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Container\Container;

class MiddlewareDispatcher
{
    private Container $container;
    // 全局中间件（所有请求都会执行）
    private array $globalMiddleware = [
        \Framework\Middleware\MethodOverrideMiddleware::class,
        // 可添加日志、CORS等全局中间件
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 调度中间件：先执行全局中间件，再执行路由中间件
     * @param Request $request
     * @param callable $next 下一个中间件/控制器
     * @return Response
     */
    public function dispatch(Request $request, callable $next): Response
    {
        // 1. 获取路由中间件（从请求的_route属性中获取）
        $route = $request->attributes->get('_route', []);
        $routeMiddleware[] = $route['middleware'] ?? [];
				#print_r($routeMiddleware);

        // 2. 合并中间件（全局 + 路由）
				//Array ( [0] => Framework\Middleware\MethodOverrideMiddleware [1] => App\Middleware\AuthMiddleware ) 
        $allMiddleware = array_merge($this->globalMiddleware, $routeMiddleware);

        // 3. 构建中间件链条（从后往前包装，确保执行顺序正确）
        $middlewareChain = $next;
				 $middleware_array = array_reverse($allMiddleware); //翻转数组
				 //print_r($middleware_array);
        foreach ($middleware_array[0] as $key => $middlewareClass) {
							if($middlewareClass == null ) continue;
            // 从容器获取中间件实例（支持中间件依赖注入）
            $middleware = $this->container->get($middlewareClass);
            // 包装中间件链条：下一个中间件作为当前中间件的$next参数
            $middlewareChain = function ($req) use ($middleware, $middlewareChain) {
                return $middleware->handle($req, $middlewareChain);
            };
        }

        // 4. 执行中间件链条（最终触发控制器）
        return $middlewareChain($request);
    }
}