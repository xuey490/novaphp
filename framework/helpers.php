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

