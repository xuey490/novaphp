<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame.
 *
 */

namespace App\Controllers;

use Framework\Utils\Captcha as CCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Captcha
{
    public function captchaImage(Request $request): Response
    {
        $config  = require __DIR__ . '/../../config/captcha.php';
        $captcha = new CCaptcha($config);
        $output = $captcha->outputImage();
		
		return $output;
    }

    public function checkCaptcha(Request $request): Response
    {
        $config    = require __DIR__ . '/../../config/captcha.php';
        $captcha   = new CCaptcha($config);
        $userInput = $request->request->get('captcha');

        if ($captcha->validate($userInput)) {
            return new Response('验证成功');
        }
        return new Response('验证码错误', 400);
    }
}
