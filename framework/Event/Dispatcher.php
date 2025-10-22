<?php

namespace Framework\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
#use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class Dispatcher implements EventDispatcherInterface
{
    private array $listeners = [];

    public function __construct( private ContainerInterface $container)
    {
    }

    /**
     * 添加监听器
     *
     * @param string $eventClass
     * @param callable|string|array $listener (类名或回调)
     * @param int $priority 越高越先执行
     */
    public function addListener(string $eventClass, callable|string|array $listener, int $priority = 0): void
    {
        $this->listeners[$eventClass][$priority][] = $listener;
        krsort($this->listeners[$eventClass]); // 按优先级排序
    }

    /**
     * 批量注册实现了 ListenerInterface 的类
     */
    public function addSubscriber(ListenerInterface $subscriber): void
    {
        foreach ($subscriber->subscribedEvents() as $event => $methods) {
            $methods = (array)$methods;
            foreach ($methods as $method) {
                $this->addListener($event, [$subscriber, $method]);
            }
        }
    }

    /**
     * 分发事件
     */
    public function dispatch(object $event): object
    {
        $eventClass = get_class($event);

        // 收集所有匹配的监听器
        $allListeners = $this->getListenersForEvent($event);

        foreach ($allListeners as $listener) {
            // 支持字符串类名（自动从 DI 容器解析）、数组回调、闭包
            if (is_string($listener) && str_contains($listener, '::')) {
                [$class, $method] = explode('::', $listener);
                $listener = [$this->container->get($class), $method];
            } elseif (is_string($listener)) {
                $listener = $this->container->get($listener);
            }

            // 执行监听器
            if (is_callable($listener)) {
                ($listener)($event);
            }

            // 如果事件标记为“可停止”，且已停止，则中断后续监听器
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }

        return $event;
    }

    /**
     * 获取某个事件的所有监听器（按优先级合并）
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = get_class($event);
        if (!isset($this->listeners[$eventClass])) {
            return [];
        }

        $flattened = [];
        foreach ($this->listeners[$eventClass] as $priorityGroup) {
            foreach ($priorityGroup as $listener) {
                $flattened[] = $listener;
            }
        }

        return $flattened;
    }

    /**
     * 是否存在监听器
     */
    public function hasListeners(object $event): bool
    {
        return !empty($this->getListenersForEvent($event));
    }
}