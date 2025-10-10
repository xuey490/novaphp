<?php

namespace App\Controller;

use Framework\Http\Request;
use Framework\Http\Response;

class UserController
{
    public function index(Request $request)
    {
        return new Response("<h1>User List</h1>");
    }

	// UserController@create
	public function create(Request $request)
	{
		$token = \Framework\Support\Csrf::tokenField();
		return new Response("<form method='post'>{$token}<input type='text' name='name'/><button>Submit</button></form>");
	}

	public function store(Request $request)
	{
		if (!\Framework\Support\Csrf::validate($request->post('_token'))) {
			return new Response('CSRF token invalid.', 403);
		}
		// 继续处理
	}

	//http://localhost:8000/user/show?id=5
    public function show(Request $request, $id = null)
    {
        $id = $request->get('id') ?: $id; // 支持 /user/show?id=5 或 /user/show/5
        return new Response("Viewing user ID: " . htmlspecialchars($id));
    }
}