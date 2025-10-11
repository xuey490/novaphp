<?php
// app/Twig/AppTwigExtension.php

namespace App\Twig;

#use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Framework\Security\CsrfTokenManager;

class AppTwigExtension extends AbstractExtension
{
    private CsrfTokenManager $tokenManager;
    private string $tokenName;
    private $session;
    private array $siteConfig;

    public function __construct(CsrfTokenManager $tokenManager, string $tokenName = '_token')
    {
        $this->tokenManager = $tokenManager;
        $this->tokenName    = $tokenName;
        $this->session      = app('session');
        $this->siteConfig   = require __DIR__ . '/../../config/site.php';

        // ⚠️ 移除下面这行，除非你真的想每次覆盖用户！
        //$this->session->set('user', 'zhangSan');

       //echo $this->tokenManager->getToken('default');
    }

    public function getGlobals(): array
    {
        return [
            'current_user' => $this->session->get('user'),
            'site'         => '我的测试站点',
            'is_mobile'    => $this->isMobile(),
        ];
    }

    public function isMobile(): bool
    {
        $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $pattern = '/Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile|Windows Phone/i';
        return (bool) preg_match($pattern, $ua); // ✅ 强制转为 true/false
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('url', [$this, 'generateUrl']),
            new TwigFunction('asset', [$this, 'asset']),
            new TwigFunction('route', [$this, 'generateRoute']),

            new TwigFunction('csrf_field', [$this, 'renderCsrfField'], ['is_safe' => ['html']]),
            new TwigFunction('csrf_token', [$this, 'getToken']),

            new TwigFunction('form_start', [$this, 'formStart'], ['is_safe' => ['html'], 'needs_context' => true]),

        ];
    }

    public function formStart(array $context, array $options = []): string
    {
        $method = $options['method'] ?? 'post';
        $action = $options['action'] ?? '';
        $methodAttr = strtoupper($method);
        $isPostLike = in_array($methodAttr, ['POST', 'PUT', 'PATCH', 'DELETE']);
    
        $html = sprintf('<form method="%s" action="%s">', htmlspecialchars($method), htmlspecialchars($action));
    
        if ($isPostLike) {
            $html .= $this->renderCsrfField();
        }
    
        return $html;
    }

    public function renderCsrfField(): string
    {
        $token = $this->tokenManager->getToken('default');

        $html = sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($this->tokenName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
        return $html;
    }

    public function getToken(): string
    {
        return $this->tokenManager->getToken('default');
    }

    public function generateUrl(string $path): string
    {
        $baseUrl = $_ENV['APP_BASE_URL'] ?? 'http://localhost';
        return $baseUrl . '/' . ltrim($path, '/');
    }

    public function asset(string $path): string
    {
        $version = $_ENV['ASSET_VERSION'] ?? '1.0';
        $prefix  = $_ENV['ASSET_CDN'] ?? '';
        return $prefix . '/assets/' . ltrim($path, '/') . '?v=' . $version;
    }

    public function generateRoute(string $name, array $params = []): string
    {
                        // TODO: 与你的路由系统集成
                        // 示例：return Router::generate($name, $params);
        return '/demo'; // 临时
    }
}
