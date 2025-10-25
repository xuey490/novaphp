<?php
// src/Http/Controller/ApiHelpersTrait.php

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiHelpersTrait
{
	/*
    protected function json(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
	*/

    /**
     * 返回成功响应
     *
     * @param array|object $data
     * @param int $code
     * @param array $headers
     * @return JsonResponse
     */
    protected function success(mixed $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return $this->json(['success' => true, 'data' => $data], $status, $headers);
    }

    /**
     * 返回错误响应
     *
     * @param string $message
     * @param int $code
     * @param array|null $details
     * @param array $headers
     * @return JsonResponse
     */
    protected function error(string $message, int $status = 400, ?array $details = null, array $headers = []): JsonResponse
    {
        return $this->json([
            'success' => false,
            'error' => array_filter([
                'message' => $message,
                'code' => $status,
                'details' => $details,
            ])
        ], $status, $headers);
    }
	
	
    /**
     * 直接返回 JSON（不包装 success/data）
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    protected function json(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        return new JsonResponse($data, $status, $headers);
    }	
}