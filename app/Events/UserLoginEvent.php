<?php
// app/Events/UserLoginEvent.php

namespace App\Events;

class UserLoginEvent
{
    public function __construct(
        public object $user,      // 例如用户模型
        public string $ip = ''
    ) {}
}