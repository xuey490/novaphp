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
			UserLoginEvent::class => 'handleUserLogin',
            UserLoggedIn::class => 'handleLoggedIn'
        ];
    }

    public function handleLoggedIn(UserLoggedIn $event): void
    {
		echo "✅ handleLoggedIn triggered User: {$event->userId}\n";
        app('log')->info("用户登录: ID={$event->userId}, IP={$event->ip}");
    }
	
    public function handleUserLogin(UserLoginEvent $event): void
    {
		//print_r($event->user->id);
		echo "✅ handleUserLogin triggered User: {$event->user->id}\n";
        app('log')->info("用户登录: ID={$event->user->id}, IP={$event->ip}");
    }
	
}