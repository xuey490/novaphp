<?php
namespace App\Controllers;

use Framework\Attributes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


#[Route(prefix: '/v2', middleware: [\App\Middlewares\AuthMiddleware::class , \App\Middlewares\LogMiddleware::class ])]
class Demo
{
    #[Route(path: '/demo/index', methods: ['GET'],  middleware: [\App\Middlewares\AuthMiddleware::class] )]
    public function list() { 
		echo 'demo list';
	}

    #[Route(path: '/demo', methods: ['POST'], middleware: [\App\Middlewares\LogMiddleware::class])]
    public function create() { 
		echo 'create';
	}
	
	 #[Route(path: '/demo/show', methods: ['GET'], middleware: [\App\Middlewares\AuthMiddleware::class])]
    public function show() { 
		echo 'demo show';
	}	
	
	
}