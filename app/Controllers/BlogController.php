<?php

// app/Controllers/BlogController.php
namespace App\Controllers;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use App\Services\BlogService; // 假设你有文章服务

class BlogController
{
    private Environment $twig;
    private BlogService $blogService;

    public function __construct(Environment $twig, BlogService $blogService)
    {
        $this->twig = $twig;
        $this->blogService = $blogService;
    }

    public function index(): Response
    {
		// 🔍 检查当前 Twig 实例是否加载了 AppTwigExtension
		$extensions = app('view')->getExtensions();
		$hasExtension = false;
		foreach ($extensions as $ext) {
			if ($ext instanceof \App\Twig\AppTwigExtension) {
				$hasExtension = true;
				//print_r($ext);
				break;
			}
		}
		if (!$hasExtension) {
			//print_r("❌ AppTwigExtension NOT registered in Twig instance!");
		} else {
			//print_r("✅ AppTwigExtension IS registered!");
		}

		
        // 动态获取文章数据
        $posts = $this->blogService->getList();
		// 热门文章数据
		$popularPosts = $this->blogService->getpopularPosts();

        $html = $this->twig->render('blog/list.html.twig', [
            'posts' => $posts,
            'current_user' => app('session')->get('user'),
            'page_title' => '最新文章',
			'popularPosts' => $popularPosts,
        ]);

        return new Response($html);
    }
}