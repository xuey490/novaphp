<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: Captcha.php
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

/**
 * Captcha Utility for NovaPHP Framework.
 *
 * Provides alnum / chinese / math captcha types with encryption-based session storage.
 */
class Captcha
{
    protected array $config;
    protected string $code;
    private string $mathExpr = '';
    private string $secretKey;

    public function __construct(array $config)
    {
        $this->config = array_merge([
            'enabled'           => true,
            'length'            => 4,
            'type'              => 'alnum', // alnum | chinese | math
            'width'             => 120,
            'height'            => 40,
            'font_size'         => 20,
            'font_path'         => null,
            'chinese_font_path' => null,
            'noise'             => true,
            'lines'             => true,
            'distortion'        => true,
            'session_key'       => 'captcha_code',
            'secret_key'        => 'nova-captcha-key', // default; can be overridden
        ], $config);

        if (! $this->config['enabled']) {
            throw new RuntimeException('Captcha is disabled.');
        }

        $this->secretKey = hash('sha256', $this->config['secret_key']); // ensure 256-bit key
    }

    /**
     * Generate captcha code and store (encrypted) in session.
     */
    public function generate(): string
    {
        switch ($this->config['type']) {
            case 'chinese':
                $this->code = $this->generateChinese();
                break;
            case 'math':
                $this->code = $this->generateMath();
                break;
            case 'alnum':
            default:
                $this->code = $this->generateAlnum();
                break;
        }

        $session = app('session');
        $encrypted = $this->encrypt($this->code);
        $session->set($this->config['session_key'], $encrypted);

        return $this->code;
    }

    /**
     * Output captcha as PNG image.
     */
	public function outputImage(): Response
	{
		$this->generate();

		$width  = (int)($this->config['width'] * $this->config['dpi_scale']);
		$height = (int)($this->config['height'] * $this->config['dpi_scale']);


		$image   = imagecreatetruecolor($width, $height);
		$bgColor = imagecolorallocate($image, 250, 250, 250);
		imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

		// 干扰线
		if ($this->config['lines']) {
			for ($i = 0; $i < 5; ++$i) {
				$lineColor = imagecolorallocate($image, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
				imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $lineColor);
			}
		}

		// 干扰点
		if ($this->config['noise']) {
			for ($i = 0; $i < 100; ++$i) {
				$dotColor = imagecolorallocate($image, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
				imagesetpixel($image, mt_rand(0, $width), mt_rand(0, $height), $dotColor);
			}
		}

		$textColor = imagecolorallocate($image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
		$fontPath  = $this->config['type'] === 'chinese'
			? $this->config['chinese_font_path']
			: $this->config['font_path'];

		$text = $this->config['type'] === 'math'
			? $this->mathExpr . ' = ?'
			: $this->code;

		// ---------------------
		// 动态字体缩放逻辑
		// ---------------------
		$fontSize = $this->config['font_size'];

		if ($fontPath && file_exists($fontPath)) {
			// 测量文字宽度，若超过画布宽度则自动缩小字体
			$maxWidth = $width * 0.9; // 预留一点边距
			$bbox     = imagettfbbox($fontSize, 0, $fontPath, $text);
			$textWidth = $bbox[2] - $bbox[0];

			while ($textWidth > $maxWidth && $fontSize > 8) {
				$fontSize -= 1;
				$bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
				$textWidth = $bbox[2] - $bbox[0];
			}

			// 计算居中坐标
			$x = (int)(($width - $textWidth) / 2);
			$y = (int)(($height + $fontSize) / 2);

			imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);
		} else {
			// fallback: 使用内置字体
			$font = 5;
			$textWidth = imagefontwidth($font) * strlen($text);
			$x = (int)(($width - $textWidth) / 2);
			$y = (int)(($height - imagefontheight($font)) / 2);
			imagestring($image, $font, $x, $y, $text, $textColor);
		}

		// 可选：文字扭曲
		if ($this->config['distortion']) {
			$distorted = imagecreatetruecolor($width, $height);
			imagefilledrectangle($distorted, 0, 0, $width, $height, $bgColor);
			for ($i = 0; $i < $width; ++$i) {
				$offset = sin($i / 10) * 3;
				for ($j = 0; $j < $height; ++$j) {
					$srcY = $j + (int)$offset;
					if ($srcY >= 0 && $srcY < $height) {
						$color = imagecolorat($image, $i, $srcY);
						imagesetpixel($distorted, $i, $j, $color);
					}
				}
			}
			imagedestroy($image);
			$image = $distorted;
		}

		// 输出 PNG
		ob_start();
		imagepng($image);
		$data = ob_get_clean();
		imagedestroy($image);

		return new Response($data, 200, [
			'Content-Type'  => 'image/png',
			'Cache-Control' => 'no-cache, no-store, must-revalidate',
			'Pragma'        => 'no-cache',
			'Expires'       => '0',
		]);
	}

    /**
     * Validate captcha input.
     */
    public function validate(string $input): bool
    {
        $session = app('session');
        $encrypted = $session->get($this->config['session_key']);
        if (empty($encrypted)) {
            return false;
        }

        $expected = $this->decrypt($encrypted);
        $session->remove($this->config['session_key']); // 仅验证一次

        if ($this->config['type'] === 'math') {
            return trim($input) === $expected;
        }

        return strtolower(trim($input)) === strtolower($expected);
    }

    // ================= Helper Generators ==================

    protected function generateAlnum(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        return substr(str_shuffle($chars), 0, $this->config['length']);
    }

    protected function generateChinese(): string
    {
        $chars = '的一是在不了有和人这中大为上个国我以要他时来用们生到作地于出就分对成会可主发年动同工也能下过子说产种面而方后多定行学法所民得经';
        $len = mb_strlen($chars, 'UTF-8');
        $result = '';
        for ($i = 0; $i < $this->config['length']; $i++) {
            $result .= mb_substr($chars, mt_rand(0, $len - 1), 1, 'UTF-8');
        }
        return $result;
    }

    protected function generateMath(): string
    {
        $a = mt_rand(1, 10);
        $b = mt_rand(1, 10);
        $op = ['+', '-', '*'][mt_rand(0, 2)];
        $expr = "{$a} {$op} {$b}";
        $this->mathExpr = $expr;
        return (string) eval("return {$expr};");
    }

    // ================= Encryption Helpers ==================

    private function encrypt(string $data): string
    {
        $iv = random_bytes(16);
        $cipherText = openssl_encrypt($data, 'AES-256-CBC', $this->secretKey, 0, $iv);
        return base64_encode($iv . $cipherText);
    }

    private function decrypt(string $data): string
    {
        $raw = base64_decode($data, true);
        if ($raw === false || strlen($raw) < 17) {
            return '';
        }
        $iv = substr($raw, 0, 16);
        $cipherText = substr($raw, 16);
        return openssl_decrypt($cipherText, 'AES-256-CBC', $this->secretKey, 0, $iv) ?: '';
    }
}




