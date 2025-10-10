<?php
// app/Middleware/AuthMiddleware.php
namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
			echo "AuthMiddleware<br>";
			//$id = $request->getSession();
        // 模拟鉴权：如果没有登录，返回401
       // if (!$id->get('user_id')) {
        //    return new Response('<h1>401 Unauthorized: Please login first</h1>', 401);
        //}

        // 鉴权通过，执行下一个中间件/控制器
        return $next($request);
    }
}