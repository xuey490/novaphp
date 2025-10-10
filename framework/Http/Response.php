<?php

namespace Framework\Http;

class Response
{
    protected $content;
    protected $status;
    protected $headers = [];

    public function __construct($content = '', $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = array_merge(['Content-Type' => 'text/html; charset=utf-8'], $headers);
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json($data, $status = 200)
    {
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->status = $status;
        $this->headers['Content-Type'] = 'application/json';
        return $this;
    }

    public function send()
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->content;
    }
}