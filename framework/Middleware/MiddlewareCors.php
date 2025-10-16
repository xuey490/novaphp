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

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareCors implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // dump('--- 进入 MiddlewareCors (中间件) ---');
        // 1. 处理预检请求 (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            $response = new Response();
        } else {
            // 2. 对其他请求，先执行后续逻辑，获取响应
            $response = $next($request);
        }

        // 3. 在响应中添加 CORS 头
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');

        // dump('--- 退出 MiddlewareCors (中间件) ---');

        // 4. 返回最终的响应
        return $response;
    }
}
