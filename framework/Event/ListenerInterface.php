<?php

namespace Framework\Event;

/**
 * 自定义监听器接口（仅用于标记和约定）
 */
interface ListenerInterface
{
    /**
     * 返回订阅的事件列表
     * 示例: [UserLoginEvent::class => 'handle']
     */
    public function subscribedEvents(): array;
}