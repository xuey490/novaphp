<?php

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\InputBag; // ← 注意：引入 InputBag

class MiddlewareXssFilter
{
    private bool $enabled = true;
    private bool $strictMode = true;

    public function __construct(bool $enabled = true, bool $strictMode = true)
    {
        $this->enabled = $enabled;
        $this->strictMode = $strictMode;
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->enabled) {
            return $next($request);
        }

        // 1. 过滤 GET 参数
        if ($request->query->count() > 0) {
            $filteredQuery = $this->filterArray($request->query->all());
            $request->query = new InputBag($filteredQuery); // ✅ 使用 InputBag
        }

        // 2. 过滤 POST 表单参数
        if ($request->request->count() > 0) {
            $filteredRequest = $this->filterArray($request->request->all());
            $request->request = new InputBag($filteredRequest); // ✅ 使用 InputBag
        }

        // 3. 过滤 JSON 请求体
        if ($request->headers->get('Content-Type') && 
            strpos($request->headers->get('Content-Type'), 'application/json') !== false) {
            
            $content = $request->getContent();
            if ($content !== '') {
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $filteredData = $this->filterArray($data);
                    $request->attributes->set('_filtered_json_body', $filteredData);
                }
            }
        }

        return $next($request);
    }

    private function filterArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }
        return $data;
    }

    private function sanitize(string $input): string
    {
        if ($this->strictMode) {
            return htmlspecialchars(strip_tags($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        } else {
            return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    public static function getFilteredJsonBody(Request $request): ?array
    {
        return $request->attributes->get('_filtered_json_body');
    }
}