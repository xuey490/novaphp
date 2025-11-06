<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: CookieManager.php
 * @Date: 2025-11-6
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

class CookieManager
{
    protected array $config;
    protected string $secret;
    protected string $cipher;
    protected string $domain;
    protected string $path;
    protected int $expire;
    protected bool $secure;
    protected bool $httponly;
    protected string $samesite;
    protected bool $encrypt;
	
    protected array $queuedCookies = [];	

    public function __construct(?string $configPath = null)
    {
        $configPath = $configPath ?? __DIR__ . '/../../config/cookie.php';
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Cookie config not found: $configPath");
        }
        $this->config = require $configPath;

        $this->secret   = (string)($this->config['secret'] ?? '');
        if (strlen($this->secret) < 16) {
            throw new \RuntimeException("Cookie secret must be at least 16 characters.");
        }

        $this->domain   = $this->config['domain'] ?? '';
        $this->path     = $this->config['path'] ?? '/';
        $this->expire   = (int)($this->config['expire'] ?? 86400);
        $this->secure   = (bool)($this->config['secure'] ?? false);
        $this->httponly = (bool)($this->config['httponly'] ?? true);
        $this->samesite = $this->config['samesite'] ?? 'Lax';
        $this->encrypt  = (bool)($this->config['encrypt'] ?? false);
        $this->cipher   = $this->config['cipher'] ?? 'AES-256-CBC';
    }


    // 队列化 Cookie
    public function queueCookie(string $name, string $value, ?int $expire = null): void
    {
        $expire = $expire ?? $this->expire;
        $data = $this->encrypt ? $this->encryptValue($value) : $value;
        $sig = $this->sign($data);
        $payload = base64_encode(json_encode(['data'=>$data,'sig'=>$sig]));

        $this->queuedCookies[] = [
            'name' => $name,
            'value' => $payload,
            'expire' => time() + $expire,
        ];
    }

    // 删除队列中的 Cookie（逻辑上删除）
    public function queueForgetCookie(string $name): void
    {
        $this->queuedCookies[] = [
            'name' => $name,
            'value' => '',
            'expire' => time() - 3600,
        ];
    }

    // 发送队列中的 Cookie（FPM 或 Workerman）
    public function sendQueuedCookies(?Response $response = null): void
    {
        foreach ($this->queuedCookies as $cookie) {
            if ($response instanceof Response) {
                $c = Cookie::create(
                    $cookie['name'],
                    $cookie['value'],
                    $cookie['expire'],
                    $this->path,
                    $this->domain ?: null,
                    $this->secure,
                    $this->httponly,
                    false,
                    ucfirst($this->samesite)
                );
                $response->headers->setCookie($c);
            } else {
                // FPM/CLI 模式
                setcookie(
                    $cookie['name'],
                    $cookie['value'],
                    [
                        'expires' => $cookie['expire'],
                        'path' => $this->path,
                        'domain' => $this->domain ?: '',
                        'secure' => $this->secure,
                        'httponly' => $this->httponly,
                        'samesite' => ucfirst($this->samesite),
                    ]
                );
                $_COOKIE[$cookie['name']] = $cookie['value'];
            }
        }

        // 清空队列
        $this->queuedCookies = [];
    }


    /**
     * 生成 Cookie 对象（不直接发送）
     */
    public function make(string $name, string $value, ?int $expire = null): Cookie
    {
        $expire = $expire ?? $this->expire;
        $data = $this->encrypt ? $this->encryptValue($value) : $value;
        $signature = $this->sign($data);

        $payload = base64_encode(json_encode([
            'data' => $data,
            'sig'  => $signature,
        ]));

        return Cookie::create(
            $name,
            $payload,
            time() + $expire,
            $this->path,
            $this->domain ?: null,
            $this->secure,
            $this->httponly,
            false,
            ucfirst($this->samesite)
        );
    }

    /**
     * 删除 Cookie 对象（返回 Symfony Cookie）
     */
    public function forget(string $name): Cookie
    {
        return Cookie::create(
            $name,
            '',
            time() - 3600,
            $this->path,
            $this->domain ?: null,
            $this->secure,
            $this->httponly,
            false,
            ucfirst($this->samesite)
        );
    }

    /**
     * 读取 Cookie 值（解密 + 验证签名）
     */
    public function get(Request $request, string $name): ?string
    {
        $cookies = $request->cookies->all();
        if (empty($cookies[$name])) {
            return null;
        }

        $raw = base64_decode($cookies[$name], true);
        if ($raw === false) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['data'], $decoded['sig'])) {
            return null;
        }

        if (!$this->verify($decoded['data'], $decoded['sig'])) {
            return null;
        }

        return $this->encrypt ? $this->decryptValue($decoded['data']) : $decoded['data'];
    }

    /**
     * 快捷在 Response 上设置 Cookie（Workerman/FPM 通用）
     */
    public function setResponseCookie(Response $response, string $name, string $value, ?int $expire = null): void
    {
        $cookie = $this->make($name, $value, $expire);
        $response->headers->setCookie($cookie);
    }

    /**
     * 快捷在 Response 上删除 Cookie
     */
    public function forgetResponseCookie(Response $response, string $name): void
    {
        $cookie = $this->forget($name);
        $response->headers->setCookie($cookie);
    }

    // ------------------------ 内部加密/签名方法 ------------------------

    protected function encryptValue(string $value): string
    {
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv = random_bytes($ivLen);
        $ciphertext = openssl_encrypt($value, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $iv);
        return $this->base64url_encode($iv . $ciphertext);
    }

    protected function decryptValue(string $encoded): string
    {
        $data = $this->base64url_decode($encoded);
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLen);
        $ciphertext = substr($data, $ivLen);
        $decrypted = openssl_decrypt($ciphertext, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $iv);
        return $decrypted ?: '';
    }

    protected function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->secret);
    }

    protected function verify(string $data, string $sig): bool
    {
        return hash_equals($this->sign($data), $sig);
    }

    protected function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64url_decode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
