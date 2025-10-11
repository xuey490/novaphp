<?php
// app/Controllers/ViewController.php
namespace App\Controllers;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ViewController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function welcome(): Response
    {

        $html = $this->twig->render('home/welcome.html.twig', [
            'name' => '访客',
            'site_name' => 'NovaPHP',
	
            'app_debug' => $_ENV['APP_DEBUG'] ?? true,
        ]);

        return new Response($html, 200, [
            'Content-Type' => 'text/html'
        ]);
    }
	
    public function index()
    {
			// 在控制器中 使用助手函数
			return view('home/index', ['name' => 'Alice']);
    }
	
		//错误测试
    public function errortest()
    {
		try {
			return new Response($this->twig->render('homes/index.html.twig', $data));
		} catch (LoaderError | RuntimeError | SyntaxError $e) {
			if ($_ENV['APP_DEBUG']) {
				throw $e; // 开发环境抛出
			}
			return new Response('模板渲染错误', 500);
		}
    }
}