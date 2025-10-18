<?php
namespace App\Controllers;

use Framework\Attributes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


#[Route(prefix: '/api/v1', middleware: [\App\Middlewares\AuthMiddleware::class , \App\Middlewares\LogMiddleware::class ])]
class DemoController
{
    #[Route(path: '/demo', methods: ['GET'], name: 'user.list' , middleware: [\App\Middlewares\AuthMiddleware::class] )]
    public function list() { 
		echo 'get list';
	}

    #[Route(path: '/demo', methods: ['POST'], middleware: [\App\Middlewares\LogMiddleware::class])]
    public function create() { 
		echo 'create';
	}
	
	 #[Route(path: '/demo/show', methods: ['GET'], middleware: [\App\Middlewares\AuthMiddleware::class])]
    public function show() { 
		echo 'show';
	}	
	
	
}