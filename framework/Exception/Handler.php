<?php

namespace Framework\Exception;

class Handler
{
    public function register()
    {
        set_exception_handler([$this, 'handle']);
        set_error_handler([$this, 'handleError']);
    }

    public function handle($exception)
    {
        $msg = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();

        error_log("PHP ERROR: {$msg} in {$file}:{$line}");

        if (php_sapi_name() === 'cli') {
            echo "Error: {$msg} in {$file}:{$line}\n";
        } else {
            http_response_code(500);
            echo "<h1>Internal Server Error</h1>";
        }
    }

    public function handleError($level, $message, $file, $line)
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }
}