<?php

// App/Controllers/UrlController.php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrlController
{

    public function edit(int $id): Response
    {
        return new Response("Edit item: ID = {$id}");
    }

    public function hello(): Response
    {
        return new Response("Hello from Attribute-based route!");
    }
}