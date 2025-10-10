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
        $this->get = $get;
        $this->post = $post;
        $this->method = strtoupper($method);
        $this->path = $path;
    }

    public function method() { return $this->method; }
    public function path() { return $this->path; }
    public function all() { return array_merge($this->get, $this->post); }
    public function get($key, $default = null) { return $this->get[$key] ?? $default; }
    public function post($key, $default = null) { return $this->post[$key] ?? $default; }
}