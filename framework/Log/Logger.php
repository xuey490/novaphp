<?php

// framework/Log/Logger.php

namespace Framework\Log;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Logger
{
    private MonoLogger $logger;

    public function __construct(string $channel = 'app')
    {
        $this->logger = new MonoLogger($channel);
        $this->logger->pushHandler(new StreamHandler(storage_path('logs/app.log'), MonoLogger::DEBUG));
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * 记录 HTTP 请求日志（使用 Symfony Request/Response）
     */
    public function logRequest(Request $request, ?Response $response = null, float $duration = 0): void
    {
        $this->info('Request', [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp() ?: 'unknown',
            'user_agent' => $request->headers->get('User-Agent') ?? 'unknown',
            'response_status' => $response?->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
        ]);
    }

    /**
     * 记录异常日志（使用 Symfony Request）
     */
    public function logException(\Throwable $exception, Request $request): void
    {
        $this->error('Exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp() ?: 'unknown',
        ]);
    }
}