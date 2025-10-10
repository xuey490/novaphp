<?php
// framework/Service/Logger.php

namespace Framework\Service;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;

class Logger
{
    private $logger;

    public function __construct($channel = 'app')
    {
        $this->logger = new MonoLogger($channel);
        $this->logger->pushHandler(new StreamHandler(storage_path('logs/app.log'), MonoLogger::DEBUG));
    }

    public function info($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $context);
    }

    public function logRequest($request, $response = null, $duration = 0)
    {
        $this->info('Request', [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'response_status' => $response?->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2)
        ]);
    }

    public function logException($exception, $request)
    {
        $this->error('Exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp()
        ]);
    }
}