<?php
namespace App\Controllers;

use Framework\Attributes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Middleware\AuthMiddleware;

#[Route(prefix: '/api/v1', middleware: [\App\Middleware\AuthMiddleware::class , \App\Middleware\LogMiddleware::class ])]
class DemoController
{
    #[Route(path: '/users', methods: ['GET'], name: 'user.list' , middleware: [\App\Middleware\AuthMiddleware::class] )]
    public function list() { 
		echo 'list';
	}

    #[Route(path: '/users', methods: ['POST'], middleware: [\App\Middleware\LogMiddleware::class])]
    public function create() { 
		echo 'create';
	}
	
	 #[Route(path: '/users/show', methods: ['GET'], middleware: [\App\Middleware\AuthMiddleware::class])]
    public function show() { 
		echo 'show';
	}	
	
	
}