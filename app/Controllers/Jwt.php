<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp.
 *
 */

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Route;
use Framework\Utils\Cookie;
use App\Middlewares\AuthMiddleware;


#[Route(prefix: '/jwts/apijwt', group: 'apijwt' )]
class Jwt
{
	private string $tokenString;
	
    public function __construct(
        private readonly Cookie $cookie
    ) {}
	
	
	public function issue()
	{
		// 登录页面登录-->获取uid，role，name-->签发token-->token存入cookie/缓存-->到下一个页面的时候
		//-->中间件请求头（或 Cookie）中提取 Token，验证 JWT 签名、issuer、exp、nbf 等标准 claims，再验证Redis 中是否存在 login:token:{jti}（用于判断是否被提前注销）-->验证失败，跳转到登录，
		$this->tokenString = app('jwt')->issue(['uid' => 42, 'name'=>'admin']);
		$token = "  Token: {$this->tokenString}<br/>";
		// app('cache')->set('jwttoken' , $this->tokenString); // jwt无状态，违背无状态、浪费内存
		// app('session')->set('jwttoken' , $this->tokenString);	//jwt无状态，违背无状态、浪费内存
		
		//解析结果
		$string = app('jwt')->getPayload($this->tokenString);
		print_r($string);
		
		
		
		//$this->cookie->make('token' , $this->tokenString);
		
		return new Response($token);
	}
	
	public function refresh()
	{

		$token = app('session')->get('jwttoken');

		// 刷新
		//$newToken = $jwt->refresh($token);
		
		//解析token
		$string = app('jwt')->getPayload($token);
		#print_r($string);
		
		return new Response('token:' . $token);
	}
	
	#[Route(path: '/', methods: ['GET'], name: 'demo1.index' , middleware: [AuthMiddleware::class])]
	public function getdatas()
	{
		return new Response('getDatas');
	}
	
	public function banner()
	{
		app('jwt')->revokeAllForUser(42);
		return new Response('kick off');
	}

	public function cookie():Response
	{
		#$this->cookie->make('token' , 'okkkkkk');
		
		$token = app('cookie')->get('token');

		return new Response('token:'.$token );
	}
	
	// 退出接口
	public function logout(Request $request): Response
	{
		$token = app('cookie')->get('token');

		if ($token) {
			app('jwt')->revoke($token);
		}
		
		//清理cookie
		app('cookie')->forget('token');	
		
		$response = new Response('logout');


		return $response;
	}
	
}
