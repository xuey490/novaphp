<?php
namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 中间件接口（所有中间件必须实现这个接口）
 */
interface MiddlewareInterface
{
    /**
     * 中间件执行方法
     * @param Request $request 请求对象
     * @param callable $next 下一个中间件/控制器的回调
     * @return Response 响应对象
     */
    public function handle(Request $request, callable $next): Response;
}