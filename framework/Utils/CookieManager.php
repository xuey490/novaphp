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

    /**
     * 设置 Cookie
     */
    public function make(string $name, string $value, ?int $expire = null): bool
    {
        $expire = $expire ?? $this->expire;
        $data = $this->encrypt ? $this->encryptValue($value) : $value;
        $signature = $this->sign($data);

        $payload = base64_encode(json_encode([
            'data' => $data,
            'sig'  => $signature,
        ]));

        return setcookie(
            $name,
            $payload,
            [
                'expires'  => time() + $expire,
                'path'     => $this->path,
                'domain'   => $this->domain ?: '',
                'secure'   => $this->secure,
                'httponly' => $this->httponly,
                'samesite' => ucfirst($this->samesite),
            ]
        );
    }

    /**
     * 获取 Cookie
     */
    public function get(string $name): ?string
    {
        if (empty($_COOKIE[$name])) {
            return null;
        }

        $raw = base64_decode($_COOKIE[$name], true);
        if ($raw === false) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['data'], $decoded['sig'])) {
            return null;
        }

        if (!$this->verify($decoded['data'], $decoded['sig'])) {
            return null; // 签名不匹配，可能被篡改
        }

        $value = $this->encrypt ? $this->decryptValue($decoded['data']) : $decoded['data'];
        return $value;
    }

    /**
     * 删除 Cookie
     */
    public function forget(string $name): void
    {
        setcookie(
            $name,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => $this->path,
                'domain'   => $this->domain ?: '',
                'secure'   => $this->secure,
                'httponly' => $this->httponly,
                'samesite' => ucfirst($this->samesite),
            ]
        );
        unset($_COOKIE[$name]);
    }

    /**
     * AES 加密
     */
    protected function encryptValue(string $value): string
    {
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv = random_bytes($ivLen);
        $ciphertext = openssl_encrypt($value, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $iv);

        // 避免 cookie 过长，采用 base64url 编码
        return $this->base64url_encode($iv . $ciphertext);
    }

    /**
     * AES 解密
     */
    protected function decryptValue(string $encoded): string
    {
        $data = $this->base64url_decode($encoded);
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLen);
        $ciphertext = substr($data, $ivLen);
        $decrypted = openssl_decrypt($ciphertext, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $iv);
        return $decrypted ?: '';
    }

    /**
     * 签名（防篡改）
     */
    protected function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->secret);
    }

    /**
     * 验证签名
     */
    protected function verify(string $data, string $sig): bool
    {
        $expected = $this->sign($data);
        return hash_equals($expected, $sig);
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