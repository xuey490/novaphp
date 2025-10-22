<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Event;

use Framework\Cache\CacheFactory;
use ReflectionClass;

class ListenerScanner
{
    private string $listenerDir;
    private CacheFactory $cache;

    public function __construct(CacheFactory $cache, string $listenerDir = null)
    {
        $this->cache = $cache;
        $this->listenerDir = BASE_PATH.'/app/Listeners';
    }

    /**
     * 获取所有监听器类名
     */
    public function getSubscribers1(): array
    {
        $cacheKey = 'event_subscribers_v2';

        //return $this->cache->set($cacheKey, 3600, function () {
            $files = glob($this->listenerDir . '/*.php');
            $subscribers = [];

            foreach ($files as $file) {
                $className = '\\App\\Listeners\\' . pathinfo($file, PATHINFO_FILENAME);
				
                if (!class_exists($className)) {
					echo $file; //输出也是空
                    require_once $file;
                }

                $ref = new ReflectionClass($className);

                if (!$ref->isInstantiable()) continue;
                if (!$ref->implementsInterface(ListenerInterface::class)) continue;

                $subscribers[] = $className;
            }
			
			print_r($subscribers); //数组是空的
			
            return $subscribers;
        //});
    }
	
	/**
	 * 获取所有监听器类名
	 */
	public function getSubscribers(): array
	{
		$listenerDir = $this->listenerDir;

		if (!is_dir($listenerDir)) {
			app('log')->info("[Event] Listeners directory not found: {$listenerDir}");
			return [];
		}

		$files = glob($listenerDir . '/*.php');
		if (!$files || !is_array($files)) {
			app('log')->info("[Event] No PHP files found in: {$listenerDir}");
			return [];
		}

		$subscribers = [];

		foreach ($files as $file) {
			// ✅ 修复点：去掉开头的反斜杠！
			$className = 'App\\Listeners\\' . pathinfo($file, PATHINFO_FILENAME);

			if (!class_exists($className, false)) {
				try {
					require_once $file;
				} catch (\Throwable $e) {
					app('log')->info("[Event] Failed to load listener file: {$file} - " . $e->getMessage());
					continue;
				}
			}

			if (!class_exists($className)) {
				app('log')->info("[Event] Class not found after loading: {$className} (file: {$file})");
				continue;
			}

			try {
				$ref = new \ReflectionClass($className);
			} catch (\ReflectionException $e) {
				app('log')->info("[Event] Reflection failed for: {$className} - " . $e->getMessage());
				continue;
			}

			if (!$ref->isInstantiable()) {
				continue;
			}

			// 可选：如果你仍然想用接口过滤，可以加上（但目前你没用，所以可选）
			if (!$ref->implementsInterface(\Framework\Event\ListenerInterface::class)) {
			     continue;
			}

			$subscribers[] = $className;
		}

		#app('log')->info("[Event] Found subscribers: " . print_r($subscribers, true));

		return $subscribers;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}