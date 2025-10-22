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
            UserLoggedIn::class => 'onUserRegistered'
        ];
    }

    public function onUserRegistered(UserLoggedIn $event): void
    {
        /*app('log')->info(
            $event->email . '欢迎加入我们！ welcome-email' . $event->name
        );
		*/
		echo "[EMAIL] 已向用户 {$event->userId} 发送欢迎邮件。\n";
    }
}