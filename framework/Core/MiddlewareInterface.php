<?php

// framework/Core/MiddlewareInterface.php

namespace Framework\Core;

use Symfony\Component\HttpFoundation\Request;
use Closure;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next);
}
