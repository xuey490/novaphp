<?php
namespace App\Controllers;

use Framework\Annotations\Get;
use Framework\Annotations\Post;
use Framework\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * 控制器级别的路由注解（可选，用于添加路径前缀）
 * @Route("/test")
 */
class TestController
{
	
	//测试熔断器
    public function circuitAction(): Response
    {
        // 方式1：抛出异常（会被中间件捕获）
        //throw new \RuntimeException('模拟后端服务崩溃');

        // 方式2：返回 500（也会被熔断器识别为失败）
        return new Response('Service error', 500);
    }
	
    /**
     * 首页路由（匹配 GET /test）
     * @Get(path="/healthy", name="test.index")
     */
    public function healthyAction(): Response
    {
        return new Response('All good!');
    }

    /**
     * 首页路由（匹配 GET /test）
     * @Get(path="/indexs", name="test.index")
     */
    public function index()
    {
        return new Response('Test Controller Index Page');
    }

	//http://www.nova.net/test8/edits/111
		/** 
		 * @Get(
		 *   path="/test8/edits/{id}",
		 *   name="test.edit",
		 *   requirements={"id": "\d+"},
		 *   options={"_middleware": {"App\Middleware\LogMiddleware",   "App\Middleware\AuthMiddleware"} }
		 * )
		 */
    public function edit(int $id)
    {
        return new Response("Edit Admin role: ID = {$id}");
    }



    /**
     * @Get(path="/hello/{name}", name="test.hello")
     */
    public function sayHello($name)
    {
        return "你好, " . htmlspecialchars($name) . "！";
    }
    
    /**
     * @Get(path="/gets/user/{id<\d+>}",  name="test.user" , options={"_middleware":  {"App\Middleware\AuthMiddleware",   "App\Middleware\LogMiddleware"}  })
     */
    public function getUser($id)
    {
        return "正在查询 ID 为 " . (int)$id . " 的用户信息...";
    }
}