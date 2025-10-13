<?php
namespace App\Controllers;
// src/Controller/HelloController.php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController
{
    #[Route('/hello')]
    public function index(): Response
    {
        // ... 你的业务逻辑 ...
        $content = 'Hello World';

        // 在返回响应之前，收集信息
        $includedFiles = get_included_files();
        $loadedClasses = get_declared_classes();
        
        // 你可以选择将信息追加到响应内容中
        $debugInfo = sprintf(
            '<hr><pre>'.
            'Included files: %d' . PHP_EOL .
            'Loaded classes: %d' . PHP_EOL .
            '</pre>',
            count($includedFiles),
            count($loadedClasses)
        );
		
		

		$symfonyFiles = array_filter($includedFiles, fn($f) => str_contains($f, '/symfony/'));
		$symfonyClasses = array_filter($loadedClasses, fn($c) => str_starts_with($c, 'Symfony\\'));

		$output = sprintf(
			'Symfony files: %d, Symfony classes: %d',
			count($symfonyFiles),
			count($symfonyClasses)
		);
		
		

        return new Response($content . $debugInfo);
    }
}