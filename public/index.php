<?php

// 框架入口文件
define('BASE_PATH', realpath(dirname(__DIR__)));
define('APP_DEBUG', true);

define('FRAMEWORK_VERSION', '0.0.10-Bate (Powered by Blue2004)');

// 引入 Composer 自动加载（确保 vendor 目录在 BASE_PATH 下）
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
} else {
    die('Composer autoload file not found. Please run "composer install".');
}

// 检查并引入框架辅助函数
$helpersFile = BASE_PATH . '/framework/helpers.php';
if (file_exists($helpersFile)) {
    require_once $helpersFile;
} else {
    die("Framework helpers file not found: $helpersFile");
}

// 检查并引入应用函数
$appFunctionsFile = BASE_PATH . '/app/function.php';
if (file_exists($appFunctionsFile)) {
    require_once $appFunctionsFile;
} else {
    die("App functions file not found: $appFunctionsFile");
}

// 启动框架
if (class_exists('\Framework\Core\Framework')) {
    $framework = new \Framework\Core\Framework();
    $framework->run();
} else {
    die('Framework core class not found.');
}

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
