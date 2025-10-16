<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp.
 *
 */

namespace App\Controllers;

use App\Middlewares\AuthMiddleware;
use App\Middlewares\LogMiddleware;
use Framework\Attributes\Route;

#[Route(prefix: '/api/v1', middleware: [AuthMiddleware::class, LogMiddleware::class])]
class DemoController
{
    #[Route(path: '/users', methods: ['GET'], name: 'user.list', middleware: [AuthMiddleware::class])]
    public function list()
    {
        echo 'list';
    }

    #[Route(path: '/users', methods: ['POST'], middleware: [LogMiddleware::class])]
    public function create()
    {
        echo 'create';
    }

    #[Route(path: '/users/show', methods: ['GET'], middleware: [AuthMiddleware::class])]
    public function show()
    {
        echo 'show';
    }
}
