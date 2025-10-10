<?php
require_once __DIR__ . '/../vendor/autoload.php';

// 加载环境变量函数
if (!function_exists('env')) {
    function env($key, $default = null) {
        // 简单实现
        return $default;
    }
}

// 加载数据库配置
$config = require __DIR__ . '/../config/database.php';

// 初始化数据库
\think\facade\Db::setConfig($config);

try {
    // 测试连接
    $result = \think\facade\Db::query('SHOW TABLES');
    echo "Database connection successful!\n";
    echo "Available tables:\n";
    foreach ($result as $row) {
        echo "- " . array_values($row)[0] . "\n";
    }
} catch (\Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
