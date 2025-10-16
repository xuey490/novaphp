<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp.
 *
 */

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;

class UrlController
{
    public function edit(int $id): Response
    {
        return new Response("Edit item: ID = {$id}");
    }

    public function hello(): Response
    {
        return new Response('Hello from Attribute-based route!');
    }
}
