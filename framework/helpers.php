<?php
// framework/helpers.php

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