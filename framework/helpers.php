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

function app_path($path = '')
{
    return base_path('app') . ($path ? '/' . $path : '');
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


if (!function_exists('view')) {
    /**
     * 渲染 Twig 模板（简化版，兼容现有逻辑）
     * @param string $template 模板路径（支持不带后缀，自动补充 .html.twig）
     * @param array $data 模板变量
     * @return string 渲染后的 HTML
     * @throws \RuntimeException 模板渲染失败时抛出异常
     */
    function view(string $template, array $data = []): string
    {
        try {
            // 1. 从容器获取 Twig 实例（确保容器中已注册 'view' 服务为 Twig\Environment）
            $twig = app('view');
            
            // 2. 自动补充模板后缀（兼容传入 'errors/debug' 或 'errors/debug.html.twig'）
            if (!str_ends_with($template, '.html.twig')) {
                $template .= '.html.twig';
            }
            
            // 3. 渲染模板（传递变量，Twig 会自动解析 {{ 变量名 }}）
            return $twig->render($template, $data);
        } catch (\Twig\Error\LoaderError $e) {
            throw new \RuntimeException("模板文件未找到：{$template}（错误：{$e->getMessage()}）", 0, $e);
        } catch (\Twig\Error\RuntimeError $e) {
            throw new \RuntimeException("模板渲染运行时错误：{$e->getMessage()}", 0, $e);
        } catch (\Twig\Error\SyntaxError $e) {
            throw new \RuntimeException("模板语法错误（行 {$e->getLine()}）：{$e->getMessage()}", 0, $e);
        } catch (\Exception $e) {
            throw new \RuntimeException("模板渲染失败：{$e->getMessage()}", 0, $e);
        }
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

/*
	*模板助手函数
	$username = 'guest';
	$name = 'ThinkPHP Template Engine';
	$version = '3.2.x';
	$features = ['Fast', 'Simple', 'Powerful'];
	$currentTime = time();
	*return ThinkView('think/thinktemp', compact('username', 'name', 'version', 'features', 'currentTime'));
*/

function ThinkView($templateName, $data = []) 
{
    $template = app('thinkTemp');
    $vars = array_merge(get_defined_vars()['data'] ?? [], $data);
    $template->assign($vars);
    return $template->fetch($templateName);
}



if (!function_exists('renders')) {
    /**
     * 渲染模板并自动分配当前作用域变量
     *
     * @param string $template 模板名，如 'user/profile'
     * @param array $data      额外要 assign 的变量（会合并并覆盖）
     * @param array $exclude   要排除的变量名（默认排除常见内部变量）
     * @return string           渲染后的 HTML 内容
     */
    function renders(string $template, array $data = [], array $exclude = null)
    {
        // 获取当前作用域所有变量
        $scopeVars = get_defined_vars();
		
				//print_r($scopeVars);

        // 默认排除列表
        $defaultExclude = ['scopeVars', 'template', 'data', 'exclude', 'args'];
        $exclude = $exclude ?? $defaultExclude;

        // 合并排除项，去重
        $exclude = array_unique(array_merge($defaultExclude, $exclude));

        // 过滤掉不需要的变量
        $filtered = array_diff_key($scopeVars, array_flip($exclude));

        // 合并作用域变量 + 手动传入的变量（后者优先级更高）
        $assignData = array_merge($filtered, $data);

        // 获取 ThinkPHP 模板引擎实例
        $tpl = app('thinkTemp'); // 确保服务名为 'thinkTemp'，根据你的绑定调整

        // 分配变量并渲染
        $tpl->assign($assignData);

        return $tpl->fetch($template);
    }
}

// 事件分发函数
function EventDispatch(object $event): object
{
    return app(\Framework\Event\Dispatcher::class)->dispatch($event);
}