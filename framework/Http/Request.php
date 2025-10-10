<?php

namespace Framework\Http;

class Request
{
    public static function capture()
    {
        return new static(
            $_GET,
            $_POST,
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/'
        );
    }

    protected $get;
    protected $post;
    protected $method;
    protected $path;

    public function __construct($get, $post, $method, $path)
    {
        $this->get = $this->sanitize($get);
        $this->post = $this->sanitize($post);
        $this->method = strtoupper($method);
        $this->path = $path;
    }

    // 安全过滤：防止 XSS（但保留原始 raw 方法）
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return is_string($data) ? htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8') : $data;
    }

    // 获取原始未过滤数据（用于开发者明确控制）
    public function raw($key, $default = null)
    {
        $source = $this->method === 'GET' ? $_GET : $_POST;
        return $source[$key] ?? $default;
    }

    // 获取已过滤数据（推荐使用）
    public function get($key, $default = null) { return $this->get[$key] ?? $default; }
    public function post($key, $default = null) { return $this->post[$key] ?? $default; }
    public function all() { return array_merge($this->get, $this->post); }
    public function method() { return $this->method; }
    public function path() { return $this->path; }
}