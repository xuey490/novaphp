<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Models\Admin;
use Framework\Middleware\MiddlewareXssFilter;
use Framework\Security\CsrfTokenManager;





class HomeController
{
    public function __construct(
        private CsrfTokenManager $csrf
    ) {}
	
	
    public function index(): Response
    {
        //getService(\Framework\Log\LoggerService::class)->info('App started');

        #$userService = getService('App\Service\UserService'); // ✅ 只要容器已 set，就可以
		#print_r( $userService->getUsers(111) );
        // ✅ 此时 app() 已可用！
		
		//dump(app()->getServiceIds()); // 查看所有服务 ID
        //$logger = app('log.logger');
        //$logger->info('Homepage visited');

	
		$cache = app('cache.manager');
		$cache->set('user_1', ['name' => 'Alice'], 3600);
		$user = $cache->clear();
		//print_r($user);
		
		
		//$cache->tag('tag')->set('name1','value1');
		//$cache->tag('tag')->set('name2','value2');
		

		//$cache->delete('user_1');
		//$cache->clear();
	
		//$cache->set('key', 'hello world!', 3600);
		//echo $cache->get('key');
		//$cache->clear();
		
		
		$session = app('session');
		// 设置一个 session 属性
		$session->set('user_id', 1283);
		// 获取一个 session 属性
		$userId = $session->get('user_id');	
		//echo $userId;
		
		
		echo trans('hello');        // 自动输出对应语言
		echo '<br />当前语言包：'.current_locale();
        return new Response('<h1>Welcome to My Framework!</h1>');
    }
	
	
	//http://localhost:8000/home/xss?name=mike<script>alert('ok');</script>
	public function xss(Request $request): Response
	{
		// 如果是 JSON 请求，使用过滤后的数据
		$data = MiddlewareXssFilter::getFilteredJsonBody($request);
		
		//if ($data === null) {
			// 可能是表单提交，用 $request->request->all()
		//	$data = $request->request->all();
		//}


		$name = $request->get('name');

		// $data 中的字符串已自动 XSS 过滤
		//$name = $data['name'] ?? '';
		// 直接输出是安全的（无需再 htmlspecialchars）
		
		return new Response("Hello, {$name}");
	}




    public function showForm(Request $request): Response
    {
        $token = $this->csrf->getToken('default');
        // 传递给模板
        return new Response("<form method='POST' action='/home/getForm'>
            <input type='hidden' name='_token' value='{$token}'>
            <input name='title'>
            <button type='submit'>Submit</button>
        </form>");
    }


    public function getForm(Request $request)
    {
		// 【可选】兜底验证（如果中间件可能失效）
		$token = $request->request->get('_token');
		if (!$this->csrf->isTokenValid('default', $token)) {
			return new Response('Invalid CSRF token.', 503);
		}
		
		$title = $request->request->get('title');
		
		return new Response("Hello, {$title}");
    }




	public function getsession(Request $request): Response
	{
		$session = $request->getSession(); // Symfony 自动绑定 session 到 Request
		$session->set('test', 'hello');
		return new Response($session->get('test'));
	}








	//列举自己需要的参数
    public function show( $id)
    {
		
        // 获取所有用户 => 返回数组数据或 json 响应
        $users = Admin::select()->toArray();
        print_r( $users ); // 因为你框架会处理 array => json
		
		//$id = $request->get('id');
        //return new Response("<h1>User ID: $id</h1>");
    }
	
	// 只获取需要的参数
	public function search1(Request $request, $roleid, $name, $status)
	{

	}

	// 或者只获取Request对象
	public function search2(Request $request)
	{
		$roleid = $request->get('roleid');
		$name = $request->get('name');
		$status = $request->get('status');
		// ...
	}

	// 混合使用
	public function search3(Request $request, $id)
	{
		// 从路由获取id，从请求中获取其他参数
		$name = $request->get('name');
		// ...
	}
	
	
}