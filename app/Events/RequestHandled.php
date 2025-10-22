<?php

namespace App\Events;

use Framework\Event\EventInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandled implements EventInterface
{
    public function __construct(
        public readonly Request $request,
        public readonly Response $response
    ) {
    }
}