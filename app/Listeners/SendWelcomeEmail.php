<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Framework\Event\ListenerInterface;

class SendWelcomeEmail  implements ListenerInterface
{
    public function __construct() {}

    public function subscribedEvents(): array
    {
        return [
            UserLoggedIn::class => ['onUserRegistered'],
        ];
    }

    public function onUserRegistered(UserLoggedIn $event): void
    {
        /*app('log')->info(
            $event->email . '欢迎加入我们！ welcome-email' . $event->name
        );
		*/
		echo "[EMAIL] 已向用户 {$event->userId} 发送欢迎邮件。\n<br>";
    }
	
	
    public function handleUserLogin(UserLoggedIn $event): void
    {
        /*app('log')->info(
            $event->email . '欢迎加入我们！ welcome-email' . $event->name
        );
		*/
		echo "Login succesfully<br>";
    }	
	
	
}

/*使用 [method, priority] 元组数组
public function subscribedEvents(): array
{
    return [
        UserLoggedIn::class => [
            ['onUserRegistered', 350],
            ['handleUserLogin', 159],
        ],
    ];
}

统一使用嵌套数组格式（推荐）
public function subscribedEvents(): array
{
    return [
        UserLoggedIn::class => [
            ['method' => 'onUserRegistered', 'priority' => 350],
            ['method' => 'handleUserLogin',   'priority' => 159],
        ],
    ];
}

简化 混合写法
public function subscribedEvents(): array
{
    return [
		UserLoggedIn::class => [
			['method' => 'onUserRegistered', 'priority' => 350],
			'handleUserLogin', // 默认 priority = 0
		],	
	];
}

更简化：UserLoggedIn::class => ['onUserRegistered' , 159],

双重事件不同响应
UserLoggedIn::class => ['onUserRegistered' , 159],
UserLoggedIn::class => ['handleUserLogin' , 300],


//混合写法会覆盖，只能执行权重较大的比如159
public function subscribedEvents(): array
{
	return [
		//UserLoggedIn::class => 'onUserRegistered'

		UserLoggedIn::class => [
			'method'   => 'onUserRegistered',
			'priority' => 350,
		],
		
		UserLoggedIn::class => ['handleUserLogin', 159],

	];
}


*/