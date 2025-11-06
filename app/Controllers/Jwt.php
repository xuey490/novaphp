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
use Framework\Utils\CookieManager;
use App\Middlewares\AuthMiddleware;


#[Route(prefix: '/jwts/apijwt', group: 'apijwt' )]
class Jwt
{
	private string $tokenString;
	
    public function __construct(
        private readonly CookieManager $cookie
    ) {}
	
	
	public function issue()
	{
		// 登录页面登录-->获取uid，role，name-->签发token-->token存入cookie/缓存-->到下一个页面的时候
		//-->中间件请求头（或 Cookie）中提取 Token，验证 JWT 签名、issuer、exp、nbf 等标准 claims，再验证Redis 中是否存在 login:token:{jti}（用于判断是否被提前注销）-->验证失败，跳转到登录，
		$this->tokenString = app('jwt')->issue(['uid' => 42, 'name'=>'admin']);
		$token = "  Token: {$this->tokenString}<br/>";

		
		app('cookie')->queueCookie('token', $this->tokenString, 3600);



		$response = new Response('非常复杂的html内容'); // 可传空字符串
		
		
		// 在发送 Response 前统一绑定队列中的 Cookie
		app('cookie')->sendQueuedCookies($response);

		// 快捷设置 Cookie
		//app('cookie')->setResponseCookie($response, 'token', $this->tokenString , 3600);

		// 快捷删除 Cookie
		//app('cookie')->forgetResponseCookie($response, 'old_cookie');

		
		//解析结果
		$string = app('jwt')->getPayload($this->tokenString);
		#print_r($string);
		
		//$this->cookie->make('token' , $this->tokenString);
		
		return $response;
		
		
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
	
	#[Route(path: '/getdatas', methods: ['GET'], name: 'demo1.index' , middleware: [AuthMiddleware::class])]
	public function getdatas()
	{
		return new Response('getDatas');
	}
	
	//banner uid=42的token
	public function banner()
	{
		app('jwt')->revokeAllForUser(42);
		return new Response('kick off');
	}

	//获取cookie，cookie字符长度单项为超过4k
	public function getcookie():Response
	{
		#$this->cookie->make('token' , 'okkkkkk');
		/*
Token:eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJOb3ZhRnJhbWUuSW5jIiwianRpIjoiYTI4MGQwYTA5MTRlNWRiYzgzOWFiZWM0YmFiNTJhMGEiLCJpc3MiOiJOb3ZhRnJhbWUiLCJpYXQiOjE3NjI0MzE2OTIuNTgxMTE3LCJuYmYiOjE3NjI0MzE2OTIuNTgxMTE3LCJleHAiOjE3NjI0MzUyOTIuNTgxMTE3LCJ1aWQiOjQyLCJuYW1lIjoiYWRtaW4ifQ.HHJ8wwQ-tqHwyBiZBGKQOcWXvz8N6lDV5rPFfwgY030	
token:eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJOb3ZhRnJhbWUuSW5jIiwianRpIjoiYTI4MGQwYTA5MTRlNWRiYzgzOWFiZWM0YmFiNTJhMGEiLCJpc3MiOiJOb3ZhRnJhbWUiLCJpYXQiOjE3NjI0MzE2OTIuNTgxMTE3LCJuYmYiOjE3NjI0MzE2OTIuNTgxMTE3LCJleHAiOjE3NjI0MzUyOTIuNTgxMTE3LCJ1aWQiOjQyLCJuYW1lIjoiYWRtaW4ifQ.HHJ8wwQ-tqHwyBiZBGKQOcWXvz8N6lDV5rPFfwgY030
		*/
		$token = app('cookie')->get('token');

		return new Response('token:'.$token );
	}
	
	
	//清理某个token
	public function revoke():Response
	{
		$token = app('cookie')->get('token');
		app('cookie')->forget('token');	
		if($token){
			app('jwt')->revoke($token);
			return new Response('revoke' );
		}
		return new Response('revoke failed' );
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
		
		$response = new Response('logout succesfully');


		return $response;
	}
	
}
