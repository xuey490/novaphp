<?php
namespace App\Controllers;

use App\Events\UserLoginEvent;
use Framework\Container\Container;
use Framework\Event\Dispatcher;
use Symfony\Component\HttpFoundation\Request;

class AuthController
{
    public function login(Request $request)
    {
        // 假设用户已验证成功
        $user = (object)['id' => 1, 'name' => 'Alice'];

        // 1. 获取事件分发器（从容器）
        $dispatcher = app(Dispatcher::class);

        // 2. 创建事件对象
        $event = new UserLoginEvent($user, $request->getClientIp() ?? '');

        // 3. 分发事件！
        $dispatcher->dispatch($event);


		
		// 获取分发器
        $dispatcher = \Framework\Container\Container::getInstance()
            ->get(\Framework\Event\Dispatcher::class);
		
		// 创建事件
        $event = new \App\Events\UserLoggedIn(
            userId: 100,
            ip: $request->getClientIp(),
            userAgent: $request->headers->get('User-Agent') ?? '',
            request: $request
        );


        $dispatcher->dispatch( $event );

		
		
    }
}