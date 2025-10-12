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
	
	
	//http://localhost:8000/view/test
	//更多用法：https://doc.thinkphp.cn/@think-template/default.html
    public function test()
    {
		// 1. 调用模板服务
        $template = app('thinkTemp');
        
	
		
        // 完整模板文件渲染 (使用绝对路径)
        // return $template->fetch(__DIR__ . '/../../templates/test.html');
		
		// 2. 变量定义
		$name ='hello world';
		
		$version ='0.0.10-Bate';
		
		$lists = ['Fast', 'Simple', 'Powerful'];
		
		// 3. 将所有需要在模板中使用的变量通过 assign 方法传递
		$template->assign([
			'name' => $name,
			'version' => $version,
			'lists' => $lists, // 注意：模板中用的是 "lists"
		]);

		// 4. 使用 Nowdoc (<<<'HTML') 来定义模板内容，防止PHP解析
		$content = <<<'HTML'
		<h1>使用ThinkPHP Template Engine</h1>
		<h2>{$name}</h2>

		<p>版本: {$version}</p>
		<ul>
		{volist name="lists" id="f"}
			<li>{$f}</li>
		{/volist}
		</ul>
		HTML;
        
        return $template->display($content);
    }


	//http://localhost:8000/view/think
	//更多用法：https://doc.thinkphp.cn/@think-template/default.html
    public function think()
    {
        // 1. 从服务容器中获取 ThinkPHP 模板引擎服务
        $template = app('thinkTemp');
		
		$username ='guest';

        // 2. 像原生 TP 一样，给模板分配变量
        $template->assign([
            'name' => 'ThinkPHP Template Engine',
            'version' => '3.2.x',
            'features' => ['Fast', 'Simple', 'Powerful'],
			'username'	=>$username,
			'currentTime'	=> time(),
        ]);
		
		
        
        // 3. 渲染并输出模板
        // 'thinktemp' 会自动拼接成 'templates/thinktemp.html' (根据您的配置)
		
        return $template->fetch('think/thinktemp');
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