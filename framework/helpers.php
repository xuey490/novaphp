<?php
// framework/helpers.php

use Framework\Core\Framework;
use Framework\Core\App;

if (!function_exists('app')) {
    /**
     * 获取服务容器或解析服务
     *
     * @param string|null $id 服务 ID
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|object
     */
    function app(?string $id = null): mixed
    {
        if ($id === null) {
            return App::getContainer();
        }

        return App::make($id);
    }
}

/*
use Framework\Core\Framework;
$container = Framework::getInstance()->getContainer();
$logger = $container->get(\Framework\Log\LoggerService::class);
$logger->info('Using container directly');
*/
if (!function_exists('getService')) {
    /**
     * 从容器中获取服务实例
     * @param string $id 服务ID（类名或别名）
     * @return object
     */
    function getService(string $id): object
    {
        $framework = Framework::getInstance(); // 假设你有单例
        return $framework->getContainer()->get($id);
    }
}

function base_path($path = '')
{
    return dirname(__DIR__) . ($path ? '/' . $path : '');
}

function storage_path($path = '')
{
    return base_path('storage') . ($path ? '/' . $path : '');
}

function config_path($path = '')
{
    return base_path('config') . ($path ? '/' . $path : '');
}

function database_path($path = '')
{
    return base_path('database') . ($path ? '/' . $path : '');
}

function env($key, $default = null)
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false) {
        return value($default);
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }

    if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
        return $matches[2];
    }

    return $value;
}


if (!function_exists('config')) {
    function config(string $key = null, $default = null)
    {
        static $config = null;
        if ($config === null) {
            // 从容器获取（需确保容器已初始化）
            $container = \Framework\Container\Container::getInstance();
            $config = $container->get('config') ?? [];
        }

        if ($key === null) {
            return $config;
        }

        // 支持点语法：database.connections.mysql
        $keys = explode('.', $key);
        $value = $config;
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}