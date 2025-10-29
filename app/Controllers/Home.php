<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp.
 *
 */

namespace App\Controllers;

use App\Models\Admin;
use Framework\Middleware\MiddlewareXssFilter;
use Framework\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Framework\Utils\Captcha as CCaptcha;

class Home
{
    public function __construct(
        private CsrfTokenManager $csrf,private RequestStack $requestStack,
    ) {}
	
	public function html():Response
	{
		

		/*
		// 创建响应实例
		$response = new Response();

		// 设置响应内容（HTML 代码）
		$response->setContent('<h1>Hello, Symfony!</h1><p>这是 HTML 内容</p>');

		// 设置 Content-Type 为 text/html
		$response->headers->set('Content-Type', 'text/html');

		// 返回响应
		return $response;
		*/

	
		// 直接在构造函数中设置 HTML 内容和类型
		$response = new Response(
			'<html><body><h1>Hello, Symfony!</h1><p>这是 HTML 内容</p></body>',
			Response::HTTP_OK, // 状态码（200）
			['Content-Type' => 'text/html'] // 响应头
		);	
		##33#
		return $response;
		
	}

    public function index(Request $request): Response
    {
		
	$session = $request->getSession();
$session->set('test', 'workerman');	
		
        // getService(\Framework\Log\LoggerService::class)->info('App started');

        # $userService = getService('App\Service\UserService'); // ✅ 只要容器已 set，就可以
        # print_r( $userService->getUsers(111) );
        // ✅ 此时 app() 已可用！

        dump(app()->getServiceIds()); // 查看所有服务 ID

        // 日志测试
        // $logger = app('log');
        // $logger->info('Homepage visited--------------------');

        // echo renderCsrfField();

        /* thinkcache测试
        $cache = app('cache');
        $cache->set('test1', ['name' => 'mike'], 3600);
        $test1 =$cache->get('test1');
        //$test1 = $cache->clear();
        print_r($test1);
        */

        // Symfony缓存
        #cache_set('user_1', ['name' => 'Alice'], 3600);
        #$user = cache_get('user_1');
        //print_r( $user );

        // $post = ['name' => 'Alice'];
        // cache_set('post_1', $post, 3600, ['posts', 'user_123']);
        // cache_set('post_2', $post, 3600, ['posts', 'category_news']);
        // 删除所有 posts 相关缓存
        // cache_invalidate_tags(['posts']);
        // cache_invalidate_tags(['user_123']);
        // print_r( cache_get('post_1') );

        // session测试
         $session = app('session');
        // 设置一个 session 属性
         $session->set('user_id', 'tom_11');
        // 获取一个 session 属性
        $userId = $session->get('user_id');
        #echo 'userId：'. $userId;

        // 配置获取测试
        // print_r(config('database'));

        // 在返回响应之前，收集信息
        $includedFiles = get_included_files();
        $loadedClasses = get_declared_classes();

        // 你可以选择将信息追加到响应内容中
        $debugInfo = sprintf(
            '<hr><pre>'
            . 'Included files: %d' . PHP_EOL
            . 'Loaded classes: %d' . PHP_EOL
            . '</pre>',
            count($includedFiles),
            count($loadedClasses)
        );


		
		// 设置 Cookie（自动签名）
		/*
		Cookie::make('theme', 'dark', 60); // 60 分钟

		// 获取
		$theme = Cookie::get('theme', 'light');

		// 删除
		Cookie::forget('theme');

		// 判断是否存在
		if (Cookie::has('cookie_consent')) {
			// ...
		}		
		*/
		
######

		##
		//Thinkphp验证##
		$data = [
			'name'  => '222',
			'age'   => 5520,
			'email' => 'thinkphp@qq.com',
		];

		$validate = new \App\Validate\NewUser;
		$result = $validate->check($data);

		if(!$result){
		#echo $validate->getError();
		}
		
###
        // echo trans('hello'); // 自动输出对应语言
        // echo '<br />当前语言包：' . current_locale();
        return new Response("<h1>Welcome to My Framework!___{$userId}</h1>");
    }

    // http://localhost:8000/home/xss?name=mike<script>alert('ok');</script>
    public function xss(Request $request): Response
    {
        // 如果是 JSON 请求，使用过滤后的数据
        $data = MiddlewareXssFilter::getFilteredJsonBody($request);

        // if ($data === null) {
        // 可能是表单提交，用 $request->request->all()
        //	$data = $request->request->all();
        // }

        $name = $request->get('name');

        // $data 中的字符串已自动 XSS 过滤
        // $name = $data['name'] ?? '';
        // 直接输出是安全的（无需再 htmlspecialchars）

        return new Response("Hello, {$name}");
    }

    public function showForm(Request $request): Response
    {
		$session = app('session');
		$userid = $session->get('user_id');
		
        $token = $this->csrf->getToken('default');
        // 传递给模板
        return new Response("<form method='POST' action='/home/checkCaptcha'>
            <input type='hidden' name='_token' value='{$token}'>
			<input type='text' name='code'>
			<input type='text' name='userid' value={$userid}>
			<img src='/captcha/captchaImage'>
            <input name='title'>
            <button type='submit'>Submit</button>
        </form>");
    }


    public function checkCaptcha(Request $request): Response
    {
        $config    = require __DIR__ . '/../../config/captcha.php';
        $captcha   = new CCaptcha($config);
        $userInput = $request->request->get('code');
		
        if (!$captcha->validate($userInput)) {
            return new Response('验证码错误'.$userInput);
        }
        return new Response('验证码正确', 200);
    }


    // CSRF token测试。
    public function getForm(Request $request)
    {
        // 1.【可选】兜底验证（如果中间件可能失效）
        $token = $request->request->get('_token');
        if (! $this->csrf->isTokenValid('default', $token)) {
            // return new Response('Invalid CSRF token.', 503);
        }

        $title = $request->request->get('title');
        return new Response("Hello, {$title}");
        // 2. 重定向到成功页面（✅ 正确）
        // return new RedirectResponse('/home/successPage');
    }

    public function successPage(Request $request)
    {
        return new Response('<h1>提交成功！</h1>');
    }

    public function getsession(Request $request): Response
    {
        //$session = $request->getSession(); // Symfony 自动绑定 session 到 Request
		$session = $this->requestStack->getSession();
        $session->set('test', 'hello');
        return new Response($session->get('test'));
    }

    public function uploadform(): Response
    {   // echo BASE_PATH;
        // $token = $this->csrf->getToken('default');
        $html = view('upload/index');
        return new Response($html);
    }

    public function upload(Request $request)
    {
        return;
        // 下面是文件上传的测试
        // 获取普通字段
        $title = $request->request->get('title'); // request->get() 也可以，但明确用 ->request 更清晰

        // 获取上传的文件（UploadedFile 对象）
        $uploadedFile = $request->files->get('image');

        if ($uploadedFile && $uploadedFile->isValid()) {
            // 文件基本信息
            $originalName = $uploadedFile->getClientOriginalName();
            $extension    = $uploadedFile->getClientOriginalExtension();
            $mimeType     = $uploadedFile->getMimeType();
            $size         = $uploadedFile->getSize(); // 字节

            // 保存文件（例如保存到 public/uploads/）
            $newFilename = uniqid() . '.' . $extension;
            $uploadedFile->move(
                BASE_PATH . '/public/uploads',
                $newFilename
            );

            // 你可以返回 JSON、重定向或渲染模板
            return json_encode([
                'title' => $title,
                'file'  => [
                    'original_name' => $originalName,
                    'saved_as'      => $newFilename,
                    'mime_type'     => $mimeType,
                    'size'          => $size,
                ],
            ]);
        }

        // 文件无效或未上传
        return json_encode(['error' => '文件上传失败'], 400);
    }

    // 列举自己需要的参数
    public function show(Request $request , int $id):Response
    {
        // 获取所有用户 => 返回数组数据或 json 响应
        //$users = Admin::select()->toArray();
        //print_r($users); // 因为你框架会处理 array => json

        $id = $request->get('id');
        return new Response("<h1>User ID: $id</h1>");
    }

    // 只获取需要的参数
    public function search1(Request $request, $roleid, $name, $status) {}

    // 或者只获取Request对象
    public function search2(Request $request):Response
    {
        $roleid = $request->get('roleid');
        $name   = $request->get('name');
        $status = $request->get('status');
        // ...
		
		return new Response("<h1>User ID: $name</h1>");
    }

    // 混合使用
    public function search3(Request $request, $id)
    {
        // 从路由获取id，从请求中获取其他参数
        $name = $request->get('name');
        // ...
    }
}
