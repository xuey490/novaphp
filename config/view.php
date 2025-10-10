<?php

// config/view.php
return [
		'driver' => 'twig',
    'paths' => [
     dirname(__DIR__) . '/resource/view',
		 dirname(__DIR__) . '/resource/acme/blog', // 第三方模块模板
        // 可添加更多
    ],
	'cache_path' =>  dirname(__DIR__) . '/storage/view', //false 不缓存
	'debug' => $_ENV['APP_DEBUG'] ?? true,
	'auto_reload' => true,
	'strict_variables' => false, //$_ENV['APP_DEBUG'] ?? true, //严格变量检查，慎用
	// 不在这里设 cache，我们用下面的方式传

];
