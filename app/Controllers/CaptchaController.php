<?php


namespace App\Controllers;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Framework\Utils\Captcha;


class CaptchaController
{
	public function captchaImage(Request $request): Response
	{
		$config = require __DIR__ . '/../../config/captcha.php';
		$captcha = new Captcha($config);
		return $captcha->outputImage();
	}
	
	

	public function checkCaptcha(Request $request): Response
	{
		$config = require __DIR__ . '/../../config/captcha.php';
		$captcha = new Captcha($config);
		$userInput = $request->request->get('captcha');

		if ($captcha->validate($userInput)) {
			return new Response('验证成功');
		} else {
			return new Response('验证码错误', 400);
		}
	}
}