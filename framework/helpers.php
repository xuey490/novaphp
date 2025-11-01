<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: helpers.php
 * @Date: 2025-10-26
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

use Framework\Container\Container;
use Framework\Core\App;
use Framework\Core\Framework;
use Framework\Security\CsrfTokenManager;
use Framework\Cache\ThinkCache;
use Framework\Cache\ThinkAdapter;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
#use Valitron\Validator;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * 开发辅助函数.
 */
function callHello(string $name): string
{
    return "Hello from a global function, {$name}!";
}

/**
 * 自定义模板函数：返回欢迎信息.
 */
function tpTemplateHello(string $name): string
{
    return "Hello, {$name}! 这是自定义模板函数的返回值";
}

/**
 * 自定义模板函数：格式化时间.
 */
function tpTemplateFormatDate(int $timestamp, string $format = 'Y-m-d H:i:s'): string
{
    return date($format, $timestamp);
}

/**
 * ThinTemplate 自动渲染中间件 CSRF token.
 */
function WebCsrfField(): string
{
    $token = app(CsrfTokenManager::class)->getToken('default');
    $field = '_token';
    return sprintf(
        '<input type="hidden" name="%s" value="%s">',
        htmlspecialchars($field, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
    );
}

/**
 * 返回 API 用的 CSRF Token 值.
 */
function APICsrfField(): string
{
    return app(CsrfTokenManager::class)->getToken('default');
}

if (!function_exists('redirectToRoute')) {
    /**
     * 根据路由名称生成 URL 并返回重定向响应.
     *
     * @throws InvalidArgumentException
     */
    function redirectToRoute(string $routeName, array $parameters = [], int $status = 302): RedirectResponse
    {
        $router = app('router');

        try {
            $url = $router->generate($routeName, $parameters);
        } catch (Throwable $e) {
            throw new InvalidArgumentException("Route '{$routeName}' not found or parameters invalid.", 0, $e);
        }

        return new RedirectResponse($url, $status);
    }
}

if (!function_exists('app')) {
    /**
     * 获取服务容器或解析服务.
     */
    function app(?string $id = null): mixed
    {
        if ($id === null) {
            return App::getContainer();
        }

        return App::make($id);
    }
}

if (!function_exists('getService')) {
    /**
     * 从容器中获取服务实例.
     */
    function getService(string $id): object
    {
        $framework = Framework::getInstance();
        return $framework->getContainer()->get($id);
    }
}







/**
 * 各路径辅助函数.
 */
function base_path(string $path = ''): string
{
    return dirname(__DIR__) . ($path !== '' ? '/' . $path : '');
}

function storage_path(string $path = ''): string
{
    return base_path('storage') . ($path !== '' ? '/' . $path : '');
}

function config_path(string $path = ''): string
{
    return base_path('config') . ($path !== '' ? '/' . $path : '');
}

function database_path(string $path = ''): string
{
    return base_path('database') . ($path !== '' ? '/' . $path : '');
}

function app_path(string $path = ''): string
{
    return base_path('app') . ($path !== '' ? '/' . $path : '');
}


/**
 * 简单缓存助手函数
 *
 * 用法：
 *   caches('foo', 'bar');       // 设置
 *   caches('foo');              // 获取
 *   caches('foo', null);        // 删除
 *   caches();                   // 返回默认实例
 */
if (!function_exists('caches')) {
    function caches(?string $key = null, mixed $value = '__GET__', ?int $ttl = null): mixed
    {
        static $instance = null;

        if ($instance === null) {
            $config = require base_path() . '/config/cache.php';
            $factory = new ThinkCache($config);
            $instance = $factory->create($config['default'] ?? 'file');
        }

        // 无参数：返回实例
        if ($key === null) {
            return $instance;
        }

        // 删除
        if ($value === null) {
            return $instance->delete($key);
        }

        // 读取
        if ($value === '__GET__') {
            return $instance->get($key);
        }

        // 写入
        return $instance->set($key, $value, $ttl);
    }
}

/**
 * 清空缓存助手
 */
if (!function_exists('caches_clear')) {
    function caches_clear(): bool
    {
        return caches()->clear();
    }
}


/**
 * 环境变量读取.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false) {
        return $default;
    }

    return match (strtolower((string)$value)) {
        'true', '(true)'   => true,
        'false', '(false)' => false,
        'empty', '(empty)' => '',
        'null', '(null)'   => null,
        default             => preg_match('/\A([\'"])(.*)\1\z/', (string)$value, $m) ? $m[2] : $value,
    };
}

if (!function_exists('config')) {
    /**
     * 配置项读取（支持点语法）.
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $container = Container::getInstance();
            /** @var array $config */
            $config = $container->get('config')->loadAll() ?? [];
        }

        if ($key === null) {
            return $config;
        }

        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

/**
 * 生成 UUID 和请求 ID.
 */
function generateUuid(): string
{
    return time() . '-' . substr(Uuid::uuid4()->toString(), 0, 16);
}

function generateRequestId(): string
{
    return 'req-' . substr(Uuid::uuid4()->toString(), 0, 8);
}

/**
 * 翻译服务.
 */
function trans(string $key, array $parameters = []): string
{
    return app('translator')->trans($key, $parameters);
}

function current_locale(): string
{
    return app('translator')->getLocale();
}

/**
 * Twig 模板渲染助手.
 */
if (!function_exists('view')) {
    function view(string $template, array $data = []): string
    {
        try {
            $twig = app('view');

            if (!str_ends_with($template, '.html.twig')) {
                $template .= '.html.twig';
            }

            return $twig->render($template, $data);
        } catch (LoaderError $e) {
            throw new RuntimeException("模板文件未找到：{$template} ({$e->getMessage()})", 0, $e);
        } catch (RuntimeError $e) {
            throw new RuntimeException("模板运行错误：{$e->getMessage()}", 0, $e);
        } catch (SyntaxError $e) {
            throw new RuntimeException("模板语法错误（行 {$e->getLine()}）：{$e->getMessage()}", 0, $e);
        } catch (Throwable $e) {
            throw new RuntimeException("模板渲染失败：{$e->getMessage()}", 0, $e);
        }
    }
}

/**
 * 缓存相关函数.
 */
if (!function_exists('cache_get')) {
    function cache_get(string $key, mixed $default = null): mixed
    {
        $cache = get_cache_instance();
        $item = $cache->getItem($key);
        return $item->isHit() ? $item->get() : $default;
    }
}

if (!function_exists('cache_set')) {
    function cache_set(string $key, mixed $value, ?int $ttl = null, array $tags = []): bool
    {
        $cache = get_cache_instance();
        $item = $cache->getItem($key);
        $item->set($value);

        if ($ttl !== null) {
            $item->expiresAfter($ttl);
        }

        if (!empty($tags)) {
            $item->tag($tags);
        }

        return $cache->save($item);
    }
}

if (!function_exists('cache_invalidate_tags')) {
    function cache_invalidate_tags(array $tags): bool
    {
        $cache = get_cache_instance();

        try {
            $cache->invalidateTags($tags);
            return true;
        } catch (Throwable $e) {
            error_log('Cache tag invalidation failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('cache_clear')) {
    function cache_clear(): bool
    {
        return get_cache_instance()->clear();
    }
}

function get_cache_instance(): TagAwareAdapter
{
    static $cache = null;

    if ($cache === null) {
        $cache = Container::getInstance()->get(TagAwareAdapter::class);
    }

    return $cache;
}

/**
 * 数据验证助手.
 *
 * @return true|array<string, string>
 */
if (!function_exists('ThinkValidate')) {
    function ThinkValidate(array $data, array $rule, array $message = []): true|array
    {
        $factory = getService(\Framework\Validation\ThinkValidatorFactory::class);
        $validator = $factory->create($rule, $message);

        if (!$validator->check($data)) {
            return $validator->getError();
        }

        return true;
    }
}

/**
 * Think 模板渲染.
 */
function ThinkView(string $templateName, array $data = []): string
{
    $template = app('thinkTemp');
    $template->assign($data);
    return $template->fetch($templateName);
}

/**
 * 通用模板渲染（带作用域变量自动分配）.
 */
if (!function_exists('renders')) {
    function renders(string $template, array $data = [], ?array $exclude = null): string
    {
        $scopeVars = get_defined_vars();

        $defaultExclude = ['scopeVars', 'template', 'data', 'exclude'];
        $exclude = array_unique(array_merge($defaultExclude, $exclude ?? []));

        $filtered = array_diff_key($scopeVars, array_flip($exclude));
        $assignData = array_merge($filtered, $data);

        $tpl = app('thinkTemp');
        $tpl->assign($assignData);

        return $tpl->fetch($template);
    }
}

/**
 * 事件分发函数.
 */
function EventDispatch(object $event): object
{
    return app(\Framework\Event\Dispatcher::class)->dispatch($event);
}
