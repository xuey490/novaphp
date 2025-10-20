<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

use Framework\Container\Container;
use Framework\Core\App;
use Framework\Core\Framework;
use Framework\Security\CsrfTokenManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Valitron\Validator;


// 开发辅助函数

function callHello(string $name): string
{
    return "Hello from a global function, {$name}!";
}

/**
 * 自定义模板函数：返回欢迎信息.
 * @param  string $name 用户名
 * @return string
 */
function tpTemplateHello($name)
{
    return "Hello, {$name}! 这是自定义模板函数的返回值";
}

/**
 * 自定义模板函数：格式化时间.
 * @param  int    $timestamp 时间戳
 * @param  string $format    格式
 * @return string
 */
function tpTemplateFormatDate($timestamp, $format = 'Y-m-d H:i:s')
{
    return date($format, $timestamp);
}

/**
 * ThinTemplate 自动渲染中间件csrf的token.
 */
function WebCsrfField(): string
{
    $token  = app(CsrfTokenManager::class)->getToken('default');
    $_token ='_token'; // token field
    return sprintf(
        '<input type="hidden" name="%s" value="%s">',
        htmlspecialchars($_token, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
    );
}

/**
 * ThinTemplate 自动渲染中间件csrf的token.
 */
function APICsrfField(): string
{
    return app(CsrfTokenManager::class)->getToken('default');
}



if (! function_exists('redirectToRoute')) {
    /**
     * 根据路由名称生成 URL 并返回重定向响应.
     *
     * @param string $routeName  路由名称（如 'home', 'post_show'）
     * @param array  $parameters 路由参数（如 ['id' => 123]）
     * @param int    $status     HTTP 状态码（默认 302）
     */
    function redirectToRoute(string $routeName, array $parameters = [], int $status = 302): RedirectResponse
    {
        $router = app('router'); // 路由服务名为 'router'

        // 生成路由 URL
        try {
            $url = $router->generate($routeName, $parameters);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Route '{$routeName}' not found or parameters invalid.");
        }

        return new RedirectResponse($url, $status);
    }
}

if (! function_exists('app')) {
    /**
     * 获取服务容器或解析服务，类似于下面的getService.
     *
     * @param  null|string               $id 服务 ID
     * @return ContainerInterface|object
     */
    function app(?string $id = null): mixed
    {
        if ($id === null) {
            return App::getContainer();
        }

        return App::make($id);
    }
}

/*
use Framework\Core\Framework;
$container = Framework::getInstance()->getContainer();
$logger = $container->get(\Framework\Log\LoggerService::class);
$logger->info('Using container directly');
*/
if (! function_exists('getService')) {
    /**
     * 从容器中获取服务实例.
     * @param string $id 服务ID（类名或别名）
     */
    function getService(string $id): object
    {
        $framework = Framework::getInstance(); // 假设你有单例
        return $framework->getContainer()->get($id);
    }
}

function base_path($path = '')
{
    return dirname(__DIR__) . ($path ? '/' . $path : '');
}

function storage_path($path = '')
{
    return base_path('storage') . ($path ? '/' . $path : '');
}

function config_path($path = '')
{
    return base_path('config') . ($path ? '/' . $path : '');
}

function database_path($path = '')
{
    return base_path('database') . ($path ? '/' . $path : '');
}

function env($key, $default = null)
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false) {
        return value($default);
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }

    if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
        return $matches[2];
    }

    return $value;
}

if (! function_exists('config')) {
    function config(?string $key = null, $default = null)
    {
        static $config = null;
        if ($config === null) {
            // 从容器获取（需确保容器已初始化）
            $container = Container::getInstance();
            $config    = $container->get('config')->loadAll() ?? [];
        }

        if ($key === null) {
            return $config;
        }

        // 支持点语法：database.connections.mysql
        $keys = explode('.', $key);

        $value = $config;
        foreach ($keys as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

function generateUuid(): string
{
    // 使用 ramsey/uuid
    return time() . '-' . substr(Uuid::uuid4()->toString(), 0, 16);
}

function generateRequestId(): string
{
    // 使用 ramsey/uuid
    return 'req-' . substr(Uuid::uuid4()->toString(), 0, 8);
}

// 翻译服务
function trans(string $key, array $parameters = []): string
{
    return app('translator')->trans($key, $parameters);
}

// 可选：获取当前语言
function current_locale(): string
{
    return app('translator')->getLocale();
}

if (! function_exists('view')) {
    function view(string $template, array $data = []): string
    {
        $twig     = app('view');
        $template = str_ends_with($template, '.html.twig') ? $template : $template . '.html.twig';
        return $twig->render($template, $data);
    }
}

// 缓存助手函数
if (! function_exists('cache_get')) {
    function cache_get(string $key, $default = null)
    {
        $cache = get_cache_instance();
        $item  = $cache->getItem($key);
        return $item->isHit() ? $item->get() : $default;
    }
}

if (! function_exists('cache_set')) {
    function cache_set(string $key, $value, ?int $ttl = null, array $tags = []): bool
    {
        $cache = get_cache_instance();
        $item  = $cache->getItem($key);

        $item->set($value);

        if ($ttl) {
            $item->expiresAfter($ttl);
        }

        if (! empty($tags)) {
            $item->tag($tags);
        }

        return $cache->save($item);
    }
}

if (! function_exists('cache_invalidate_tags')) {
    function cache_invalidate_tags(array $tags): bool
    {
        $cache = get_cache_instance();
        try {
            $cache->invalidateTags($tags);
            return true;
        } catch (Exception $e) {
            error_log('Cache tag invalidation failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (! function_exists('cache_clear')) {
    function cache_clear(): bool
    {
        $cache = get_cache_instance();
        return $cache->clear();
    }
}

function get_cache_instance(): TagAwareAdapter
{
    static $cache = null;
    if ($cache === null) {
        $container = Container::getInstance();
        $cache     = $container->get(TagAwareAdapter::class);
    }
    return $cache;
}


// 验证器
if (!function_exists('validator')) {
    function validator(array $data, array $rules, string $lang = 'en'): array
    {
        // 尝试从容器获取工厂
        if (function_exists('getService')) {
            /** @var ValidatorFactory $factory */
            $factory = getService(\Framework\Validation\ValidatorFactory::class);
            $v = $factory->create($data, array_keys($rules), $lang);
        } else {
            // 回退：直接实例化（适用于测试或非容器环境）
            Validator::lang($lang);
            $v = new Validator($data, array_keys($rules));
        }

        // 解析规则（支持字符串或数组）
        foreach ($rules as $field => $ruleList) {
            if (is_string($ruleList)) {
                $ruleList = explode('|', $ruleList);
            }

            foreach ($ruleList as $rule) {
                if (is_string($rule)) {
                    if (strpos($rule, ':') !== false) {
                        [$method, $param] = explode(':', $rule, 2);
                        $v->rule($method, $field, $param);
                    } else {
                        $v->rule($rule, $field);
                    }
                }
            }
        }

        $v->validate();
        return $v->errors();
    }
}

/**
 * 数据验证助手函数
 * @param array $data 待验证数据
 * @param array $rule 验证规则
 * @param array $message 自定义提示
 * @return array|true 验证通过返回true，失败返回错误信息数组
 */
if (!function_exists('ThinkValidate')) {
	function ThinkValidate(array $data, array $rule, array $message = [])
	{
		/** @var ContainerInterface $container */
		$factory = getService(\Framework\Validation\ThinkValidatorFactory::class);
		$validator = $factory->create($rule, $message);

		if (!$validator->check($data)) {
			print_r($validator->getError());
			return $validator->getError(); // 返回错误信息
		}
		return true;
	}
}