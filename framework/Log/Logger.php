<?php

// framework/Log/Logger.php

namespace Framework\Log;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ServerRequestInterface;

class Logger
{
    private $logger;

    public function __construct($channel = 'app')
    {
        $this->logger = new MonoLogger($channel);
        $this->logger->pushHandler(new StreamHandler(storage_path('logs/app.log'), MonoLogger::DEBUG));
        //print_r($this->logger);
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

    public function logRequest(ServerRequestInterface $request, ?object $response = null, float $duration = 0): void
    {
        $this->info('Request', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
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
            'uri' => (string) $request->getUri(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}
