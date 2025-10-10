<?php

// framework/Log/LoggerService.php

namespace Framework\Log;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Config\ConfigService;
use Monolog\Handler\FilterHandler;
use Monolog\Formatter\JsonFormatter;

class LoggerService
{
    private MonoLogger $logger;

    public function __construct(
        private ConfigService $config
    ) {
        $channel = $this->config->get('log.log_channel', 'app');
        $logDir = $this->config->get('log.log_path', BASE_PATH . '/storage/logs');

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }


        $requestId = generateRequestId(); // 如：req-5f3a9b2c

        /*
        // 1. 检查 Monolog API 版本
        echo "Monolog API version: " . \Monolog\Logger::API . "\n";

        // 2. 检查 RotatingFileHandler 文件位置
        $ref = new \ReflectionClass(\Monolog\Handler\RotatingFileHandler::class);
        echo "RotatingFileHandler loaded from: " . $ref->getFileName() . "\n";

        // 3. 检查是否存在 setSize 方法
        if (method_exists(\Monolog\Handler\RotatingFileHandler::class, 'setSize')) {
            echo "✅ setSize() method exists.\n";
        } else {
            echo "❌ setSize() method does NOT exist!\n";
            $methods = get_class_methods(\Monolog\Handler\RotatingFileHandler::class);
            echo "Available methods: " . implode(', ', $methods) . "\n";
        }

        exit;
        */


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


    /**
     * 将 "10M"、"5G" 等字符串转换为字节数
     */
    private function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $value = (int) $size;

        switch ($last) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
                break;
            default:
                // Assume bytes if no suffix
                break;
        }

        return $value;
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

    // 可选：暴露底层 logger 用于其他级别
    public function getMonoLogger(): MonoLogger
    {
        return $this->logger;
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
