<?php

// 防止直接访问
defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';

// 启动应用
$application = new Framework\Core\Application(BASE_PATH);
$application->run();