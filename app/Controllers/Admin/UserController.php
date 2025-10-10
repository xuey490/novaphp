<?php
namespace App\Controllers\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController
{
    public function index()
    {
        return new Response('<h1>Admin - User List- index</h1>');
    }

    public function edit(Request $request)
    {
		$id = $request->get('id');
        return new Response("<h1>$id,Admin Edit User ID: $id</h1>");
    }
}