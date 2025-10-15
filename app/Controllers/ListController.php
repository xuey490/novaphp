<?php

namespace App\Controllers;

use Framework\Attributes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


#[Route(prefix: '/lists', middleware: [\App\Middlewares\AuthMiddleware::class , \App\Middlewares\LogMiddleware::class ])]
class ListController 
{
    #[Route(path:'/', methods: ['GET'])]
    public function index(Request $request)
    {
        echo 'index';
    }

    #[Route(path:'/profile', methods: ['GET'])]

	/*
	@Middleware(class="App\Middlewares\AuthMiddleware")
	*/
    public function profile(Request $request)
    {
        echo 'profile';
    }

    #[Route(path:'/get/{id}', methods: ['GET'])]
    public function show(Request $request, string $id)
    {
        echo 'show: ' . $id;
    }
}