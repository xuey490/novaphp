<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Models\Admin;


class HomeController
{
    public function index(): Response
    {
        //getService(\Framework\Log\LoggerService::class)->info('App started');

        return new Response('<h1>Welcome to My Framework!</h1>');
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