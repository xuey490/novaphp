<?php

// app/Controllers/UploadController.php
namespace App\Controllers;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Utils\FileUploader;

class UploadController
{
    private Environment $twig;

	private FileUploader $uploader;

    public function __construct(Environment $twig, FileUploader $uploader)
    {
        $this->twig = $twig;
        $this->uploader = $uploader;
    }


	public function form()		
	{
		//echo generateUuid();
		$html = $this->twig->render('upload/form.html.twig');
		return new Response($html);
	}
	

    public function process(Request $request)
    {
        try {
            $result = $this->uploader->upload($request);
            return json_encode(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
	
}