<?php

namespace Framework\Middleware;

use Framework\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MiddlewareCsrfProtection
{
    private CsrfTokenManager $tokenManager;
    private string $tokenName;
    private array $except;

    /**
     * @param CsrfTokenManager $tokenManager
     * @param string $tokenName 表单中的 token 字段名（如 _token）
     * @param array $except 跳过的路径（支持通配符，如 '/api/*'）
     */
    public function __construct(
        CsrfTokenManager $tokenManager,
        string $tokenName = '_token',
        array $except = []
    ) {
        $this->tokenManager = $tokenManager;
        $this->tokenName = $tokenName;
        $this->except = $except;
    }

	public function handle(Request $request, callable $next): Response
	{
		// 跳过 HEAD, OPTIONS, TRACE
		if (in_array($request->getMethod(), ['HEAD', 'OPTIONS', 'TRACE'])) {
			return $next($request);
		}

		// GET 请求：注入 token 到 attributes（供模板使用）
		if ($request->getMethod() === 'GET') {
			$request->attributes->set('csrf_token', $this->tokenManager->getToken('default'));
			return $next($request);
		}

		// 跳过例外路径
		foreach ($this->except as $pattern) {
			$regex = preg_quote($pattern, '#');
			$regex = str_replace('\*', '.*', $regex);
			if (preg_match('#^' . $regex . '$#', $request->getPathInfo())) {
				return $next($request);
			}
		}

		// 验证 CSRF token
		$token = $request->request->get($this->tokenName)
				?? $request->headers->get('X-CSRF-TOKEN')
				?? '';

		if (!is_string($token) || !$this->tokenManager->isTokenValid('default', $token)) {
			throw new AccessDeniedHttpException('Invalid CSRF token.');
		}
		
		//$this->tokenManager->removeToken('default');

		return $next($request);
	}
	

}