<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp.
 *
 */

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthMiddleware
{
	
	public function handle(Request $request, callable $next): Response
	{
		# dump('--- 进入 AuthMiddleware (中间件) ---');
		$token = app('cookie')->get('token') ?? $request->headers->get('Authorization')?->replace('Bearer ', '');

		if (!$token) {
			 return new Response('<h1>401 Unauthorized: Please login first</h1>', 401);
			//return new \Symfony\Component\HttpFoundation\RedirectResponse('/jwt/issue', 301);
		}

		try {
			$parsed = app('jwt')->parse($token); // 内部已包含 JWT 验证 + Redis 检查
			$request->attributes->set('user_claims', $parsed->claims()->all());
		} catch (\Exception $e) {
			 return new Response('<h1>401 Token is expired</h1>', 401);
			//return new \Symfony\Component\HttpFoundation\RedirectResponse('/jwt/issue', 301);
		}

        // 鉴权通过，执行下一个中间件/控制器
        return $next($request);
        # dump('--- 退出 AuthMiddleware (中间件) ---');
	}

}
