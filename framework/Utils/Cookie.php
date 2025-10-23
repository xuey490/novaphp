<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: Cookie.php
 * @Date: 2025-10-23
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use RuntimeException;

class Cookie
{
    /**
     * 默认配置（可通过 config/session.php 或环境变量覆盖）
     */
    protected static array $defaults = [
        'path'        => '/',
        'domain'      => null,       // 自动检测 host
        'secure'      => false,      // HTTPS 下自动设为 true
        'httponly'    => true,
        'samesite'    => 'lax',      // 可选: 'lax', 'strict', 'none'
        'expires'     => 0,          // 0 = 会话 cookie
        'encrypt'     => false,      // 是否加密值
        'secret'      => '',         // 签名密钥（必须设置）
    ];

    /**
     * 初始化默认配置
     */
    public static function setup(array $options): void
    {
        foreach ($options as $key => $value) {
            if (array_key_exists($key, self::$defaults)) {
                self::$defaults[$key] = $value;
            }
        }

        // 强制要求 secret
        if (empty(self::$defaults['secret'])) {
            throw new RuntimeException('Cookie signing secret is required.');
        }
    }

    /**
     * 创建并发送 Cookie
     *
     * @param string $name
     * @param mixed  $value
     * @param int    $minutes 有效期（分钟），0 表示会话 cookie
     * @return bool
     */
    public static function make(string $name, $value, int $minutes = 0): bool
    {
        $options = self::buildOptions($minutes);
        $value   = self::prepareValue($value, $options);

        return setcookie(
            $name,
            $value,
            [
                'expires'  => $options['expires'],
                'path'     => $options['path'],
                'domain'   => $options['domain'],
                'secure'   => $options['secure'],
                'httponly' => $options['httponly'],
                'samesite' => $options['samesite'],
            ]
        );
    }

    /**
     * 删除 Cookie（设为过期）
     */
    public static function forget(string $name): bool
    {
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path'    => self::$defaults['path'],
            'domain'  => self::$defaults['domain'] ?: '',
            'secure'  => self::$defaults['secure'],
            'httponly' => self::$defaults['httponly'],
            'samesite' => self::$defaults['samesite'],
        ]);
    }

    /**
     * 获取 Cookie 值（自动验证签名/解密）
     */
    public static function get(string $name, $default = null): mixed
    {
        if (!isset($_COOKIE[$name])) {
            return $default;
        }

        $value = $_COOKIE[$name];

        return self::extractValue($value) ?? $default;
    }

    /**
     * 检查 Cookie 是否存在
     */
    public static function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    // ================================
    // 内部方法
    // ================================

    /**
     * 构建选项数组
     */
    protected static function buildOptions(int $minutes): array
    {
        $now = time();

        return [
            'expires'  => $minutes > 0 ? $now + ($minutes * 60) : 0,
            'path'     => self::$defaults['path'],
            'domain'   => self::$defaults['domain'] ?? parse_url($_SERVER['HTTP_HOST'] ?? '', PHP_URL_HOST),
            'secure'   => self::$defaults['secure'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => self::$defaults['httponly'],
            'samesite' => self::$defaults['samesite'],
            'encrypt'  => self::$defaults['encrypt'],
        ];
    }

    /**
     * 准备写入的值：序列化 + 签名 + 可选加密
     */
    protected static function prepareValue($value, array $options): string
    {
        $raw = serialize($value);
        $signed = $raw . '.' . self::sign($raw);

        if ($options['encrypt'] && function_exists('openssl_encrypt')) {
            $iv = openssl_random_pseudo_bytes(16);
            $encrypted = openssl_encrypt($signed, 'AES-256-CBC', self::$defaults['secret'], 0, $iv);
            return base64_encode($iv . $encrypted);
        }

        return base64_encode($signed);
    }

    /**
     * 提取并验证 Cookie 值
     */
    protected static function extractValue(string $value): ?string
    {
        $decoded = base64_decode($value, true);
        if (!$decoded) {
            return null;
        }

        $secret = self::$defaults['secret'];

        // 检查是否加密
        if (self::$defaults['encrypt'] && strlen($decoded) > 16) {
            $iv = substr($decoded, 0, 16);
            $data = substr($decoded, 16);
            $decrypted = openssl_decrypt($data, 'AES-256-CBC', $secret, 0, $iv);
            if (!$decrypted) {
                return null;
            }
            $decoded = $decrypted;
        }

        // 验证签名
        if (!str_contains($decoded, '.')) {
            return null;
        }

        [$raw, $signature] = explode('.', $decoded, 2);
        if (!self::isSafeEqual($signature, self::sign($raw))) {
            return null; // 签名不匹配，拒绝
        }

        return unserialize($raw);
    }

    /**
     * 生成签名
     */
    protected static function sign(string $value): string
    {
        return hash_hmac('sha256', $value, self::$defaults['secret']);
    }

    /**
     * 安全的字符串比较（防时序攻击）
     */
    protected static function isSafeEqual(string $known, string $user): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($known, $user);
        }

        // PHP < 5.6 fallback
        if (strlen($known) !== strlen($user)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < strlen($known); $i++) {
            $result |= ord($known[$i]) ^ ord($user[$i]);
        }

        return $result === 0;
    }
}