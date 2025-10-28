<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Log;

use Framework\Config\ConfigService;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;
use Psr\Log\LoggerInterface;  // 导入 LoggerInterface
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stringable;  // 引入 Stringable 接口

class LoggerService implements LoggerInterface  // 实现 Psr\Log\LoggerInterface
{
    private MonoLogger $logger;

    public function __construct(
        private ConfigService $config
    ) {
        $channel = $this->config->get('log.log_channel', 'app');
        $logDir  = $this->config->get('log.log_path', BASE_PATH . '/storage/logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->logger = new MonoLogger($channel);

        // 1. Debug 日志：仅 DEBUG
        $debugHandler = new FilterHandler(
            new StreamHandler($logDir . '/debug.log', MonoLogger::DEBUG),
            MonoLogger::DEBUG,
            MonoLogger::DEBUG
        );
        $this->logger->pushHandler($debugHandler);

        // 2. Error 日志：ERROR ~ EMERGENCY
        $errorHandler = new FilterHandler(
            new StreamHandler($logDir . '/error.log', MonoLogger::ERROR),
            MonoLogger::ERROR,
            MonoLogger::EMERGENCY
        );
        $this->logger->pushHandler($errorHandler);

        // 3. App 日志：INFO, NOTICE, WARNING
        $appHandler = new FilterHandler(
            new StreamHandler($logDir . '/app.log', MonoLogger::INFO),
            MonoLogger::INFO,
            MonoLogger::WARNING
        );
        $this->logger->pushHandler($appHandler);
    }

    // 实现 Psr\Log\LoggerInterface 的方法，修改为 Stringable|string
    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function getMonoLogger(): MonoLogger
    {
        return $this->logger;
    }

    public function logRequest(Request $request, ?Response $response = null, float $duration = 0): void
    {
        $this->info('Request', [
            'method'          => $request->getMethod(),
            'uri'             => $request->getRequestUri(),
            'ip'              => $request->getClientIp() ?: 'unknown',
            'user_agent'      => $request->headers->get('User-Agent') ?? 'unknown',
            'response_status' => $response?->getStatusCode(),
            'duration_ms'     => round($duration * 1000, 2),
        ]);
    }

    public function logException(\Throwable $exception, Request $request): void
    {
        $this->error('Exception', [
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
            'method'  => $request->getMethod(),
            'uri'     => $request->getRequestUri(),
            'ip'      => $request->getClientIp() ?: 'unknown',
        ]);
    }
}
