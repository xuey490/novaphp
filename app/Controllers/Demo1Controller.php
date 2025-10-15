<?php
namespace App\Controllers;

use Framework\Attributes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\LogMiddleware;


#[Route(prefix: '/api/v1/demo', group: 'api', middleware: [AuthMiddleware::class, LogMiddleware::class])]
class Demo1Controller
{
	#http://localhost:8000/api/v1/demo/
    #[Route(path: '/', methods: ['GET'], name: 'user.index')]
    public function index(Request $request): Response
    {
        return new Response(json_encode([
            'message' => 'User list fetched successfully',
            'method' => 'GET',
            'controller' => __METHOD__,
        ]), 200, ['Content-Type' => 'application/json']);
    }

	#http://localhost:8000/api/v1/demo/
    #[Route(path: '/', methods: ['POST'], name: 'user.create', middleware: [LogMiddleware::class])]
    public function create(Request $request): Response
    {
        return new Response(json_encode([
            'message' => 'New user created',
            'method' => 'POST',
            'controller' => __METHOD__,
        ]), 201, ['Content-Type' => 'application/json']);
    }

	#http://localhost:8000/api/v1/demo/1234
    #[Route(path: '/{id}', methods: ['DELETE'], name: 'user.delete')]
    public function delete(Request $request, $id): Response
    {
        return new Response(json_encode([
            'message' => "User #$id deleted",
            'method' => 'DELETE',
            'controller' => __METHOD__,
        ]), 200, ['Content-Type' => 'application/json']);
    }
}
