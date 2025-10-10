<?php
// config/routes.php
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

// 手动路由（优先级高）- 修正后
$routes->add('admin_home', new Route(
    '/index', // 路由路径（注意：若已开启.html后缀处理，这里无需加.html）
    [
        '_controller' => 'App\Controllers\HomeController::index' // 标准格式：类名::方法名
    ],
    [], // 路由参数约束（可选）
    [], // 路由选项（可选）
    '', // 主机名（可选）
    [], //  schemes（http/https，可选）
    ['GET'] // 允许的请求方法（可选，建议明确指定）
));

// 示例：带参数的手动路由（如 /api/user/123）
$routes->add('api_user', new Route(
    '/apis/user/{id}', // 带参数的路径
    [
        '_controller' => 'App\Controllers\Api\UserController::show',
        'id' => 1 // 参数默认值（可选）
    ],
    [
        'id' => '\d+' // 参数约束：id必须是数字（可选，增强路由安全性）
    ],
    [],
    '',
    [],
    ['GET']
));



$routes->add('admin.dashboard', new Route(
    '/admin/dashboard',
    ['_controller' => 'App\Controllers\Admin\DashboardController::index'],
    [],
    ['_middleware' => ['App\Middleware\AdminAuthMiddleware']]
));


return $routes;