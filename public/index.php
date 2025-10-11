<?php

// 定义项目根目录常量（可选）
define('BASE_PATH', dirname(__DIR__));
define('APP_DEBUG', true);

define('FRAMEWORK_VERSION', '0.0.10-Bate (Powered by Blue2004)');

// 引入 Composer 自动加载
require_once BASE_PATH . '/vendor/autoload.php';

require_once __DIR__ . '/../framework/helpers.php';


// 假设这是您加载路由的代码
/*
$routeLoader = new Framework\Core\AnnotationRouteLoader(
    __DIR__ . '/../app/Controllers', // 您的控制器目录
    'App\Controllers'                 // 您的控制器命名空间
);
$routes = $routeLoader->load(); // 获取路由集合

// --- 添加调试代码 ---
echo "<pre>";
echo "Loaded Route Collection:\n";

foreach ($routes as $name => $route) {
    echo "Name: $name\n";
    echo "Path: " . $route->getPath() . "\n";
    echo "Controller: " . $route->getDefault('_controller') . "\n";
    echo "Methods: " . implode(', ', $route->getMethods()) . "\n";
    echo "---\n";
}
echo "</pre>";

foreach ($routes as $routeName => $symfonyRoute) {
    // 只打印目标路由（user.show）的信息
    if ($routeName === 'user.show') {
        echo "<pre>";
        echo "路由 {$routeName} 的配置：\n";
        // 打印路由的 options（中间件配置在这里）
        print_r($symfonyRoute->getOptions());
        echo "</pre>";
        die; // 打印后终止，方便查看
    }
}
*/
//die("Routing debug output complete.");

// 启动框架
$framework = new \Framework\Core\Framework();
$framework->run();

