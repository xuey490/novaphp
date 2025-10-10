<?php
// framework/Core/ExceptionHandler.php
namespace Framework\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExceptionHandler
{
    public function handle(Throwable $e, Request $request): Response
    {
        // 判断环境
        $isProd = getenv('APP_ENV') === 'production';
        
        // 构建响应
        if ($isProd) {
            // 生产环境：显示友好错误页面
            $content = $this->renderFriendlyError($e);
        } else {
            // 开发环境：显示详细错误信息
            $content = $this->renderDetailedError($e);
        }
        
        // 记录错误日志
        $this->logError($e);
        
        // 返回响应
        $statusCode = $this->getStatusCode($e);
        return new Response($content, $statusCode, [
            'Content-Type' => 'text/html; charset=UTF-8'
        ]);
    }
    
    // 其他辅助方法...
}