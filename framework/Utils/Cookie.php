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
    protected array $config;


    /**
     * 初始化默认配置
     */
    public function __construct(array $config)
    {
        // 合并默认值
        $defaults = [
            'path'     => '/',
            'domain'   => null,
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'lax',
            'expires'  => 0,
            'encrypt'  => true,
            'secret'   => '',
        ];

        $this->config = array_merge($defaults, $config);

        if (empty($this->config['secret'])) {
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
    public function make(string $name, $value, int $minutes = 0): bool
    {
        $options = $this->buildOptions($minutes);
        $value   = $this->prepareValue($value, $options);

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
    public function forget(string $name): bool
    {
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path'    => $this->config['path'],
            'domain'  => $this->config['domain'] ?: '',
            'secure'  => $this->config['secure'],
            'httponly' => $this->config['httponly'],
            'samesite' => $this->config['samesite'],
        ]);
    }

    /**
     * 获取 Cookie 值（自动验证签名/解密）
     */
    public function get(string $name, $default = null): mixed
    {
        if (!isset($_COOKIE[$name])) {
            return $default;
        }

        $value = $_COOKIE[$name];
        return $this->extractValue($value) ?? $default;
    }
	
    /**
     * 检查 Cookie 是否存在
     */
    public function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    // ================================
    // 内部方法（非 static）
    // ================================


    /**
     * 构建选项数组
     */
    protected function buildOptions(int $minutes): array
    {
        $now = time();
        $domain = $this->config['domain'] ?? parse_url($_SERVER['HTTP_HOST'] ?? '', PHP_URL_HOST);
		
		// 如果未手动设置 domain，且 host 是 localhost / IP，则设为 null
		if ($domain === null && $host) {
			$host = explode(':', $host)[0]; // 去掉端口
			if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
				$domain = ''; // 或 null，setcookie 会忽略
			} else {
				$domain = $host;
			}
		}

        return [
            'expires'  => $minutes > 0 ? $now + ($minutes * 60) : 0,
            'path'     => $this->config['path'],
            'domain'   => $domain,
            'secure'   => $this->config['secure'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => $this->config['httponly'],
            'samesite' => $this->config['samesite'],
            'encrypt'  => $this->config['encrypt'],
        ];
    }

	protected function prepareValue($value, array $options): string
	{
		$raw = serialize($value);

		// base64加密
		return base64_encode($raw);
	}

	protected function extractValue(string $value): mixed
	{
		$decoded = base64_decode($value, true);
		if (!$decoded) return null;

		return unserialize($decoded);
	}


}