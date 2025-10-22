<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Framework\Event\ListenerInterface;

class SendWelcomeEmail  implements ListenerInterface
{
    public function __construct() {}

    public function subscribedEvents(): array
    {
        return [
            UserRegistered::class => 'onUserRegistered'
        ];
    }

    public function onUserRegistered(UserRegistered $event): void
    {
        app('log')->info(
            $event->email . '欢迎加入我们！ welcome-email' . $event->name
        );
    }
}