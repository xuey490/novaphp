<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp.
 *
 */

namespace App\Controllers;

use App\Services\BlogService;
use App\Twig\AppTwigExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

 // 假设你有文章服务

class BlogController
{
    private Environment $twig;

    private BlogService $blogService;

    public function __construct(Environment $twig, BlogService $blogService)
    {
        $this->twig        = $twig;
        $this->blogService = $blogService;
    }
	
	public function list(Request $request)
	{
		$total = 150;
		
		$pagination = new \Framework\Utils\Pagination($total, $request, 10);
		
		$pageData =$pagination->getData(2);
		

		
		return $this->twig->render('blog/list.html.twig', [
			'pagination' => $pageData,
		]);
		
	}

    public function index(): Response
    {
        // 🔍 检查当前 Twig 实例是否加载了 AppTwigExtension
        $extensions   = app('view')->getExtensions();
        $hasExtension = false;
        foreach ($extensions as $ext) {
            if ($ext instanceof AppTwigExtension) {
                $hasExtension = true;
                // print_r($ext);
                break;
            }
        }
        if (! $hasExtension) {
            // print_r("❌ AppTwigExtension NOT registered in Twig instance!");
        }
        // print_r("✅ AppTwigExtension IS registered!");

        $mdContent = $this->twig->render('makedown.html.twig', ['title' => 'Hello']);

        // 动态获取文章数据
        $posts = $this->blogService->getList();
        // 热门文章数据
        $popularPosts = $this->blogService->getpopularPosts();

        $html = $this->twig->render('blog/index.html.twig', [
            'posts'        => $posts,
            'current_user' => app('session')->get('user'),
            'page_title'   => '最新文章',
            'popularPosts' => $popularPosts,
            'mdContent'    => $mdContent,
        ]);

        return new Response($html);
    }
}
