<?php
// app/Events/UserLoginEvent.php

namespace App\Events;

use Framework\Event\EventInterface;

class UserLoginEvent implements EventInterface
{
    public function __construct(
        public object $user,      // 例如用户模型
        public string $ip = ''
    ) {}
}