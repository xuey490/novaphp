<?php

namespace App\Controller;

use Framework\Http\Response;
use App\Model\User;

class HomeController
{
    public function index()
    {
        return new Response("<h1>Welcome to My PHP Framework!</h1>");
    }
	
	
    public function show()
    {
        return new Response ('show here');
    }

    public function submit()
    {
        return new Response("Form submitted via POST!", 200);
    }
}