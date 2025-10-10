<?php
// 文件路径: /App/Controllers/UserController.php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Middleware\AuthMiddleware; // 引入你的中间件
use Symfony\Component\Routing\Annotation\Route as Router;
#use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Framework\Annotations\Middleware; 


/**
 * @Router("/list") // 控制器级别的路由前缀
 */
class ListController 
{
    /**
     * @Router("/", methods={"GET"})
     *
     * 这个方法不需要中间件，可以直接访问。
     */
    public function index(Request $request)
    {
        echo 'index';
    }

    /**
     * @Router("/profile", methods={"GET"})
     * @Middleware(class=AuthMiddleware::class)
     *
     * 这个方法需要通过 AuthMiddleware 验证后才能访问。
     */
    public function profile(Request $request)
    {

        echo 'profile';
    }

    /**
     * @Router("/get/{id}", methods={"GET"})
     * @Middleware(class=AuthMiddleware::class)
     *
     * 这个方法同样需要认证，并且演示了如何获取路由参数。
     */
    public function show(Request $request)
    {
        echo 'show';
    }
}