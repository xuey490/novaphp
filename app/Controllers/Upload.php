<?php

//declare(strict_types=1);

/**
 * This file is part of NovaFrame.
 *
 */

namespace App\Controllers;

use Framework\Utils\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class Upload
{
    private Environment $twig;

    private FileUploader $uploader;

    public function __construct(Environment $twig, FileUploader $uploader)
    {
        $this->twig     = $twig;
        $this->uploader = $uploader;
    }

    public function form()
    {
        // echo generateUuid();
        $html = $this->twig->render('upload/form.html.twig');
        return new Response($html);
    }

	public function process(Request $request)
	{

        try {
            $result = $this->uploader->upload($request , 'file');
            return json_encode(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()], 400);
        }

    }
	
	public function testme()
	{
		return new Response('TESTME OK');
	}
	
}
