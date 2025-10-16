<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp.
 *
 */

use Framework\Security\CsrfTokenManager;

// 开发辅助函数

function callHello(string $name): string
{
    return "Hello from a global function, {$name}!";
}

/**
 * 自定义模板函数：返回欢迎信息.
 * @param  string $name 用户名
 * @return string
 */
function tpTemplateHello($name)
{
    return "Hello, {$name}! 这是自定义模板函数的返回值";
}

/**
 * 自定义模板函数：格式化时间.
 * @param  int    $timestamp 时间戳
 * @param  string $format    格式
 * @return string
 */
function tpTemplateFormatDate($timestamp, $format = 'Y-m-d H:i:s')
{
    return date($format, $timestamp);
}

/**
 * ThinTemplate 自动渲染中间件csrf的token.
 */
function WebCsrfField(): string
{
    $token  = app(CsrfTokenManager::class)->getToken('default');
    $_token ='_token'; // token field
    return sprintf(
        '<input type="hidden" name="%s" value="%s">',
        htmlspecialchars($_token, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
    );
}

/**
 * ThinTemplate 自动渲染中间件csrf的token.
 */
function APICsrfField(): string
{
    return app(CsrfTokenManager::class)->getToken('default');
}
