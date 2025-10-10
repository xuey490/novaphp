<?php

namespace Framework\Core;

use Framework\Http\Request;
use Framework\Routing\Router;
use Framework\Exception\Handler;

class Application
{
    protected $basePath;
    protected $container;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();

		// 在 Application::__construct() 中添加：
		//\think\facade\Db::setConfig(require $this->basePath . '/config/database.php');
        // 注册错误处理器
        (new Handler())->register();
    }

    public function run()
    {
        try {
            $request = Request::capture();

            $router = new Router($this->basePath);
            $response = $router->dispatch($request);

            $response->send();
        } catch (\Exception $e) {
            (new Handler())->handle($e);
        }
    }

    public function basePath($path = '')
    {
        return $this->basePath . ($path ? '/' . $path : '');
    }

    public function config($key, $default = null)
    {
        static $config = [];
        if (!$config) {
            foreach (glob($this->basePath('/config/*.php')) as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $config[$name] = require $file;
            }
        }

        return array_get($config, $key, $default);
    }
}

// 辅助函数：array_get（简化数组取值）
if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;
        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }
}