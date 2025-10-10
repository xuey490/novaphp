<?php
// Framework/Core/Exception/ErrorHandler.php
namespace Framework\Core\Exception;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Throwable;

class ErrorHandler
{
    private Environment $twig;
    private bool $debug;

    public function __construct(Environment $twig, bool $debug = false)
    {
        $this->twig = $twig;
        $this->debug = $debug;
    }

    public function handle(Throwable $e): Response
    {
        $status = $e->getCode();
        $status = is_numeric($status) && $status >= 400 && $status < 600 ? $status : 500;

        $template = "errors/{$status}.html.twig";
        $data = $this->debug ? ['message' => $e->getMessage()] : [];

        try {
            $html = $this->twig->render($template, $data);
        } catch (\Twig\Error\LoaderError $loaderError) {
            // 如果错误模板也找不到，使用纯 HTML
            $html = "<h1>Error {$status}</h1><p>An error occurred.</p>";
            if ($this->debug) {
                $html .= "<pre>{$e->getMessage()}</pre>";
            }
        }

        return new Response($html, $status);
    }
}