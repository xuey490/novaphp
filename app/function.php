<?php

//开发辅助函数

function callHello(string $name):string {
    return "Hello from a global function, {$name}!";
}

/**
 * 自定义模板函数：返回欢迎信息
 * @param string $name 用户名
 * @return string
 */
function tpTemplateHello($name) {
    return "Hello, {$name}! 这是自定义模板函数的返回值";
}

/**
 * 自定义模板函数：格式化时间
 * @param int $timestamp 时间戳
 * @param string $format 格式
 * @return string
 */
function tpTemplateFormatDate($timestamp, $format = 'Y-m-d H:i:s') {
    return date($format, $timestamp);
}