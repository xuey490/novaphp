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
        \Framework\Middleware\MiddlewareMethodOverride::class,
        \Framework\Middleware\MiddlewareCors::class,
        \Framework\Middleware\MiddlewareRateLimit::class,
        //\Framework\Middleware\MiddlewareCircuitBreaker::class, //熔断中间件，正式环境使用，开发环境直接溢出错误堆栈
        \Framework\Middleware\MiddlewareIpBlock::class,
        \Framework\Middleware\MiddlewareXssFilter::class,
        \Framework\Middleware\MiddlewareCsrfProtection::class,
        \Framework\Middleware\MiddlewareRefererCheck::class,
        // 添加日志、CORS、熔断器、限流器，xss、 ip block等全局中间件
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
        // 假设路由定义中的 middleware 可能是多维或混合的，我们先获取它
        $rawRouteMiddleware = $route['middleware'] ?? [];

        // 2. 【核心步骤】规范化路由中间件数组
        // 将可能嵌套的多维数组合并成一维数组
        $flattenedRouteMiddleware = $this->flattenArray($rawRouteMiddleware);
        
        // 从路由中间件中移除掉那些已经在全局中间件中定义过的项，避免重复执行
        $uniqueRouteMiddleware = array_values(array_diff(
            $flattenedRouteMiddleware, 
            $this->globalMiddleware
        ));
        
        // 3. 合并中间件（全局 + 干净的路由中间件）
        // 这将得到你期望的顺序：[全局1, 全局2, 路由1, 路由2]
        $allMiddleware = array_merge($this->globalMiddleware, $uniqueRouteMiddleware);

        // 4. 构建中间件链条（从后往前包装，确保执行顺序正确）
        $middlewareChain = $next;
        // 关键：翻转合并后的数组
        $reversedMiddleware = array_reverse($allMiddleware);
		
		//dump($this->container->getServiceIds()); //已经成功

		//var_dump(class_exists(\Framework\Middleware\MiddlewareRateLimit::class)); // 应该是 true

        foreach ($reversedMiddleware as $middlewareClass) {
            // 跳过可能存在的空值
            if (empty($middlewareClass)) {
                continue;
            }
			
			/**	用于测试调试		
			$middleware = $this->container->get($middlewareClass);
			dump("Loaded middleware: " . get_class($middleware));
			// 如果是 RateLimit，打印其 cacheDir
			if ($middleware instanceof \Framework\Middleware\MiddlewareRateLimit) {
				$ref = new \ReflectionClass($middleware);
				$prop = $ref->getProperty('cacheDir');
				$prop->setAccessible(true);
				dump("Cache dir: " . $prop->getValue($middleware));
			}*/
            
            // 从容器获取中间件实例
            $middleware = $this->container->get($middlewareClass);
            
            // 包装中间件链条
            $middlewareChain = function ($req) use ($middleware, $middlewareChain) {
                return $middleware->handle($req, $middlewareChain);
            };
        }

        // 5. 执行中间件链条（最终触发控制器）
        return $middlewareChain($request);
    }
    
    /**
     * 将多维数组递归“拍平”成一维数组
     * @param array $array
     * @return array
     */
    private function flattenArray(array $array): array
    {
        $result = [];
        array_walk_recursive($array, function($value) use (&$result) {
            $result[] = $value;
        });
        return $result;
    }
}
