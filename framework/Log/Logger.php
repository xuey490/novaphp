<?php

namespace Framework\Log;

class Logger
{
    protected $channel;

    public function __construct($channel = 'single')
    {
        $this->channel = $channel;
    }

    public function info($message)
    {
        $this->write("INFO: {$message}");
    }

    public function error($message)
    {
        $this->write("ERROR: {$message}");
    }

    protected function write($message)
    {
        $config = include BASE_PATH . '/config/log.php';
        $path = $config['channels'][$this->channel]['path'];
        file_put_contents($path, date('Y-m-d H:i:s') . " - {$message}\n", FILE_APPEND);
    }
}