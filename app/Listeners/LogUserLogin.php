<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Events\UserLoginEvent;
use Framework\Event\ListenerInterface;

class LogUserLogin implements ListenerInterface
{
	//监听器subscribedEvents
    public function subscribedEvents(): array
    {
        return [
			//UserLoginEvent::class => 'handleUserLogin',
        UserLoginEvent::class => [
            'method'   => 'handleUserLogin',
            'priority' => 200,
        ],
        UserLoggedIn::class => [
            'method'   => 'handleLoggedIn',
            'priority' => 100,
        ],			
			
            //UserLoggedIn::class => 'handleLoggedIn'
        ];
    }

    public function handleLoggedIn(UserLoggedIn $event): void
    {
				//print_r($event);
				echo "✅ handleLoggedIn triggered User: {$event->userId}\r\n<br>";
        app('log')->info("用户登录: ID={$event->userId}, IP={$event->ip} ");
    }
	
    public function handleUserLogin(UserLoginEvent $event): void
    {
		//print_r($event->user->id);
		echo "✅ handleUserLogin triggered User: {$event->user->id}\r\n<br>";
        app('log')->info("用户登录: ID={$event->user->id}, IP={$event->ip}");
    }
	
}