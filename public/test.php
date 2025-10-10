<?php
/**
 * NovaPHP 框架入口文件
 * 
 * 入口文件职责：
 * 1. 定义核心常量
 * 2. 初始化错误处理
 * 3. 加载自动加载器
 * 4. 启动框架核心
 * 
 * @author Your Name
 * @version 1.0
 */

// 1. 禁止直接访问目录
if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
}

// 2. 定义核心常量（路径相关）
define('ROOT_PATH', dirname(__DIR__));          // 项目根目录
define('APP_PATH', ROOT_PATH . '/app');         // 应用目录（控制器、模型等）
define('FRAMEWORK_PATH', ROOT_PATH . '/framework'); // 框架核心目录
define('CONFIG_PATH', ROOT_PATH . '/config');   // 配置文件目录
define('STORAGE_PATH', ROOT_PATH . '/storage'); // 存储目录（缓存、日志等）
define('PUBLIC_PATH', __DIR__);                 // 公共目录（入口文件所在目录）

// 3. 定义环境常量（开发/生产环境）
// 可通过服务器环境变量、.env文件或直接定义切换环境
define('APP_ENV', getenv('APP_ENV') ?: 'development'); // development/production

// 4. 初始化错误处理（根据环境区分）
if (APP_ENV === 'development') {
    // 开发环境：显示所有错误
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // 生产环境：隐藏错误显示，记录到日志
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    // 确保日志目录可写
    $logDir = STORAGE_PATH . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/php_error_' . date('Ymd') . '.log');
}

// 5. 加载Composer自动加载器（必须优先加载）
$autoloadFile = ROOT_PATH . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Fatal Error: Composer autoload file not found! ' . PHP_EOL;
    echo 'Please run "composer install" in the project root directory.';
    exit(1);
}
require $autoloadFile;

// 6. 加载环境配置（可选，推荐使用.env文件）
// 如果使用vlucas/phpdotenv组件，可添加以下代码（需先执行composer require vlucas/phpdotenv）
/*
$dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
try {
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // 无.env文件时忽略（生产环境可能不使用.env）
    if (APP_ENV === 'development') {
        trigger_error('Warning: .env file not found in project root.', E_USER_WARNING);
    }
}
*/

// 7. 启动框架核心
try {
	require_once __DIR__ . '/../framework/helpers.php';

    // 实例化框架核心类
    $framework = new \Framework\Core\Framework();
    
    // 启动框架（内部会处理路由分发、控制器执行等）
    $framework->run();
} catch (\Exception $e) {
    // 捕获框架运行时异常
    header('HTTP/1.1 500 Internal Server Error');
    
    if (APP_ENV === 'development') {
        // 开发环境：显示详细异常信息
        echo '<pre style="background:#f8f8f8;padding:15px;border-left:5px solid #dc3545;">';
        echo 'Exception: ' . $e->getMessage() . PHP_EOL;
        echo 'File: ' . $e->getFile() . ' (Line: ' . $e->getLine() . ')' . PHP_EOL;
        echo 'Trace:' . PHP_EOL . $e->getTraceAsString();
        echo '</pre>';
    } else {
        // 生产环境：显示友好提示，记录详细日志
        echo 'Sorry, something went wrong. Our team has been notified.';
        
        // 记录异常到日志
        $logContent = sprintf(
            "[%s] Exception: %s\nFile: %s (Line: %d)\nTrace: %s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        file_put_contents(STORAGE_PATH . '/logs/framework_error_' . date('Ymd') . '.log', $logContent, FILE_APPEND);
    }
    exit(1);
}