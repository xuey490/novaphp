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
        $this->headers = $headers;
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
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