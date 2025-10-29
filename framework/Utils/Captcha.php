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
use Symfony\Component\HttpFoundation\Request;

class Captcha
{
    protected $config;

    protected $code;

    private string $mathExpr;
	
	#private $request;

    public function __construct(array $config)
    {
		
        if (! isset($_SESSION)) {
            //    session_start();
        }

        $this->config = array_merge([
            'enabled'           => true,
            'length'            => 4,
            'type'              => 'alnum',
            'width'             => 120,
            'height'            => 40,
            'font_size'         => 20,
            'font_path'         => null,
            'chinese_font_path' => null,
            'noise'             => true,
            'lines'             => true,
            'distortion'        => true,
            'session_key'       => 'captcha_code',
        ], $config);

        if (! $this->config['enabled']) {
            throw new \RuntimeException('Captcha is disabled.');
        }
    }

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
		$session->set($this->config['session_key'], $this->code);
        // $_SESSION[$this->config['session_key']] = $this->code;
        //app('session')->set($this->config['session_key'], $this->code);
        //print_r(app('session')->get($this->config['session_key']));
        return $this->code;
    }

    public function outputImage(): Response
    {
        $this->generate();

        $width  = $this->config['width'];
        $height = $this->config['height'];

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

        // 文字
        // print_r($this->config['chinese_font_path']);
        // die();
        $textColor = imagecolorallocate($image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        $fontPath  = $this->config['type'] === 'chinese' ? $this->config['chinese_font_path'] : $this->config['font_path'];

        if ($this->config['type'] === 'math') {
            $text = $this->mathExpr . ' = ?';
        } else {
            $text = $this->code;
        }

        if ($fontPath && file_exists($fontPath)) {
            $bbox      = imagettfbbox($this->config['font_size'], 0, $fontPath, $text);
            $textWidth = $bbox[2] - $bbox[0];
            $x         = intval(($width - $textWidth) / 2); // 强制转换为整数
            $y         = intval(($height + $this->config['font_size']) / 2); // 强制转换为整数

            // 使用 imagettftext 绘制文字
            imagettftext($image, $this->config['font_size'], 0, $x, $y, $textColor, $fontPath, $text);

            // 如果启用了扭曲，则对已含文字的图像进行扭曲
            if ($this->config['distortion']) {
                $newImage = imagecreatetruecolor($width, $height);
                imagefilledrectangle($newImage, 0, 0, $width, $height, $bgColor);

                for ($i = 0; $i < $width; ++$i) {
                    $offset = sin($i / 10) * 3; // 波浪幅度可配置
                    for ($j = 0; $j < $height; ++$j) {
                        $srcY = $j + (int) $offset;
                        if ($srcY >= 0 && $srcY < $height) {
                            $color = imagecolorat($image, $i, $srcY);
                            imagesetpixel($newImage, $i, $j, $color);
                        }
                    }
                }

                imagedestroy($image);
                $image = $newImage;
            }
        } else {
            // fallback to basic gd font (no distortion support for simplicity)
            imagestring($image, 5, 10, 10, $text, $textColor);
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return new Response($imageData, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    public function validate(string $input): bool
    {
		$session = app('session');
        if ($session->get($this->config['session_key']) =='') {
            return false;
        }

        $expected = $session->get($this->config['session_key']);

        if ($this->config['type'] === 'math') {
            return trim($input) === $expected;
        }
		$session->set($this->config['session_key'], null);
        return strtolower(trim($input)) === strtolower($expected);
    }

    protected function generateAlnum(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        return substr(str_shuffle($chars), 0, $this->config['length']);
    }

    protected function generateChinese(): string
    {
        // 简体中文常用字（可扩展）
        $chars  = '的一是在不了有和人这中大为上个国我以要他时来用们生到作地于出就分对成会可主发年动同工也能下过子说产种面而方后多定行学法所民得经十三之进着等部度家电力里如水化高自二理起小物现实加量都两体制机当使点从业本去把性好应开它合还因由其些然前外天政四日那社义事平形相全表间样与关各重新线内数正心反你明看原又么利比或但质气第向道命此变条只没结解问意建月公无系军很情者最立代想已通并提直题党程展五果料象员革位入常文总次品式活设及管特件长求老头基资边流路级少图山统接知较将组见计别她手角期根论运农指几九区强放决西被干做必战先回则任取据处';
        $len    = mb_strlen($chars, 'UTF-8');
        $result = '';
        for ($i = 0; $i < $this->config['length']; ++$i) {
            $index = mt_rand(0, $len - 1);
            $result .= mb_substr($chars, $index, 1, 'UTF-8');
        }
        return $result;
    }

    protected function generateMath(): string
    {
        $a      = mt_rand(1, 10);
        $b      = mt_rand(1, 10);
        $op     = ['+', '-', '*'][mt_rand(0, 2)];
        $expr   = "{$a} {$op} {$b}";
        $answer = 0;
        switch ($op) {
            case '+': $answer = $a + $b;
                break;
            case '-': $answer = $a - $b;
                break;
            case '*': $answer = $a * $b;
                break;
        }
        // 存储答案用于验证，显示表达式

        $this->mathExpr = (string) $expr;

        return (string) $answer;
    }
}
