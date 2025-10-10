<?php
// framework/Log/LoggerService.php

namespace Framework\Log;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Config\ConfigService;

class LoggerService
{
    private MonoLogger $logger;

    // 直接依赖 ConfigService（推荐）
    public function __construct(
        private ConfigService $config
    ) {
        $channel = $this->config->get('log.log_channel', 'app');
        $logPath = $this->config->get('log.log_path', BASE_PATH . '/storage/logs/app.log');

        $this->logger = new \Monolog\Logger($channel);
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($logPath, \Monolog\Logger::DEBUG));
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

    public function logRequest(ServerRequestInterface $request, ?object $response = null, float $duration = 0): void
    {
        $this->info('Request', [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'response_status' => $response?->getStatusCode() ?? null,
            'duration_ms' => round($duration * 1000, 2)
        ]);
    }

    public function logException(\Throwable $exception, ServerRequestInterface $request): void
    {
        $this->error('Exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}