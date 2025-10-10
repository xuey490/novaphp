<?php
// framework/Core/Router.php
namespace Framework\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Framework\Middleware\MethodOverrideMiddleware;
use Framework\Container\Container; // 引入你的静态容器
use Psr\Container\ContainerInterface; // 推荐使用 PSR-11 标准接口


class Router
{
	
    private $allRoutes; // 所有路由集合（手动路由 + 注解路由）
	
    private $controllerNamespace = 'App\\Controllers'; // 默认控制器命名空间

    // 新增：用于存储 DI 容器
    private $container;
	
    /**
     * 构造函数：仅接收合并后的路由集合（职责单一化）
     * @param RouteCollection $allRoutes 合并后的所有路由（手动 + 注解）
     * @param string $controllerNamespace 控制器基础命名空间（可选，默认 App\Controllers）
     */
    public function __construct(
        RouteCollection $allRoutes,
		ContainerInterface $container, // <--- 新增参数 // ← 期望 PSR-11 容器
        string $controllerNamespace = 'App\\Controllers'
		
    ) {
        $this->allRoutes = $allRoutes;
		$this->container = $container; // <--- 存储容器
        $this->controllerNamespace = $controllerNamespace;
		
    }
	
	
	/**
	 * 加载注解路由
	 */
	private function loadAnnotationRoutes()
	{
		$loader = new AnnotationRouteLoader(
			$this->controllerDir,
			$this->controllerNamespace
		);
		$this->annotationRoutes = $loader->loadRoutes();
	}
	
	
    /**
     * 路由分发核心方法
     */
    public function dispatch(Request $request)
    {
		
		// 步骤 0: 应用中间件
		$this->applyMiddleware($request);
		
		// 步骤 1: 去除URL中的.html后缀（路由解析前统一处理）
		$this->removeHtmlSuffix($request);
	
        $path = $request->getPathInfo();
        $context = new RequestContext();
        $context->fromRequest($request);

        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();

        // Step 1: 优先匹配 手动路由 + 注解路由（已合并到 allRoutes）
        try {
            $matcher = new UrlMatcher($this->allRoutes, $context);
            $parameters = $matcher->match($path);
            $request->attributes->add($parameters);

            // 解析并执行控制器
            $controller = $controllerResolver->getController($request);
            $arguments = $argumentResolver->getArguments($request, $controller);
            return call_user_func_array($controller, $arguments);
        } catch (ResourceNotFoundException $e) {
            // 合并路由未匹配，尝试自动路由
        }

        // Step 2: 尝试自动路由（最低优先级）
        //[$controllerFqcn, $method, $vars] = $this->resolveAutoRoute($path, $request);
		list($controllerFqcn, $method, $vars) =$this->resolveAutoRoute($path, $request);

        if ($controllerFqcn && class_exists($controllerFqcn) && method_exists($controllerFqcn, $method)) {
            $request->attributes->set('controller', $controllerFqcn);
            $request->attributes->set('action', $method);

            // 实例化控制器并执行
            $controllerInstance = $this->instantiateController($controllerFqcn);
			//var_dump($controllerInstance);
            $controller = [$controllerInstance, $method];
            $arguments = $this->extractMethodArguments($controller, $vars, $request);

            return call_user_func_array($controller, $arguments);
        }

        // 所有路由均未匹配，返回 404
        return new \Symfony\Component\HttpFoundation\Response('Error: 404 Not Found!', 404);
    }
	
	/**
	 * 去除URL路径中的.html后缀
	 */
	private function removeHtmlSuffix(Request $request)
	{
		$path = $request->getPathInfo(); // 获取当前路径（如"/user/show.html"）
		
		// 正则匹配并去除末尾的.html（仅匹配路径部分，不影响查询参数?id=5&name=mike）
		$cleanPath = preg_replace('/\.html$/', '', $path);
		
		// 更新请求的路径信息（确保后续路由解析使用干净的路径）
		$request->server->set('REQUEST_URI', str_replace($path, $cleanPath, $request->getUri()));
		$request->initialize(
			$request->query->all(),
			$request->request->all(),
			$request->attributes->all(),
			$request->cookies->all(),
			$request->files->all(),
			$request->server->all(),
			$request->getContent()
		);
	}



	/*暂不可用*/
	private function resolveAutoRoute2(string $path): array
	{
		/*
		http://localhost:8000/admin/user/edit?id=111	无法访问 404 not found

		http://localhost:8000/user/add		正常访问，映射UserController@show(5)
		http://localhost:8000/user/show/5		正常访问，映射UserController@show(5)
		http://localhost:8000/user/show?id=5		正常访问，映射UserController@show(5)
		http://localhost:8000/			正常访问，映射HomeController@home
		http://localhost:8000/home/			正常访问，映射HomeController@home
		http://localhost:8000/home/index		正常访问，映射HomeController@home
		http://localhost:8000/show/5			无法访问404 not found
		http://localhost:8000/show?id=5		正常访问，映射HomeController@show(5)
		http://localhost:8000/home/show?id=5	正常访问，映射HomeController@show(5)
		http://localhost:8000/home/show/5		正常访问，映射HomeController@show(5)
		*/			
		
		
		//获取所有request
		$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
		
		$method = $request->getMethod();
		//将url按/分解
		$parts = array_filter(explode('/', $path));
		
		$vars = [];

		// 默认控制器和方法
		$controllerParts = [];
		$action = null;

		// RESTful 映射
		$restMap = [
			'GET'    => ['index', 'show', 'create', 'edit'],
			'POST'   => ['store'],
			'PUT'    => ['update'],
			'DELETE' => ['destroy'],
		];

		// 如果路径为空，首页
		if (empty($parts)) {
			return ["{$this->controllerNamespace}HomeController", 'index', []];
		}
		
		// 尝试匹配 RESTful 动作
		if (isset($restMap[$method]) && count($parts) === 1) {
			// 例如 GET /user → UserController@index
			//GET http://localhost:8000/user 或 http://localhost:8000/user/ 自动匹配 UserController@index
			$controllerName = ucfirst($parts[1]) . 'Controller';
			$action = in_array('index', $restMap[$method]) ? 'index' : $restMap[$method][0];
			$controllerParts = [$controllerName];
		} elseif (isset($restMap[$method]) && count($parts) >= 2) {
			//http://localhost:8000/user/show/123
			$first = reset($parts);	//user
			$last = end($parts);	//123
			$secondLast = prev($parts);
			//print_r($secondLast);
			//http://localhost:8000/user/111
			//print_r($last); $last =111;

			// 检查是否是 ID（数字）
			if (is_numeric($last)) {
				$action = in_array('show', $restMap[$method]) ? 'show' : $restMap[$method][0];
				//$controllerName = ucfirst($secondLast) . 'Controller';
				//$controllerParts = [ucfirst($secondLast) . 'Controller'];
				$controllerName = ucfirst($first) . 'Controller';
				$controllerParts = [ucfirst($first) . 'Controller'];
				//print_r($controllerParts);
				$vars['id'] = $last;
			} else {
				http://localhost:8000/user/add /user/add ->UserController@add
				// 例如 /user/create → UserController@create
				//$controllerName = ucfirst($secondLast) . 'Controller';
				$controllerName = ucfirst($first) . 'Controller';
				$action = $last;
				//print_r($controllerName);
				$controllerParts = [$controllerName];
				if (in_array($action, $restMap[$method])) {
					$controllerParts = [$controllerName];
				}
			}
		}

		// fallback: 按路径层级找控制器
		if (empty($controllerParts)) {
			$controllerParts = array_map('ucfirst', $parts);
			$lastIndex = count($controllerParts) - 1;
			$controllerParts[$lastIndex] .= 'Controller';
			$action = $action ?? 'index';
		} else {
			$action = $action ?? 'index';
		}

		$className = $this->controllerNamespace . implode('\\', $controllerParts);

		return [$className, $action, $vars];
	}
	


	// 解析自动路由
	//修复http://localhost:8000/home/show/666
	private function resolveAutoRoute(string $path, Request $request): array
	{
		$path = rtrim($path, '/');
		$parts = array_filter(explode('/', $path)); // 拆分路径为数组（如 "/user/show/1" → ["user", "show", "1"]）
		$method = $request->getMethod();
		$vars = []; // 存储提取的参数

		// 根路径特殊处理：映射到 HomeController@index
		if ($path === '') {
			$controllerFqcn = "{$this->controllerNamespace}\\HomeController";
			return class_exists($controllerFqcn) && method_exists($controllerFqcn, 'index')
				? [$controllerFqcn, 'index', []]
				: [null, null, []];
		}

		// RESTful 动作优先级（用于无显式动作时的默认匹配）
		$restPriority = [
			'GET'    => ['index', 'show', 'create', 'edit'],
			'POST'   => ['store', 'create'],
			'PUT'    => ['update', 'edit'],
			'DELETE' => ['destroy'],
		];

		// -------------- 核心修复：支持「控制器/动作/参数」格式 --------------
		// 场景1：路径段 ≥3（如 ["user", "show", "1"] → 控制器=User, 动作=show, 参数=1）
		if (count($parts) >= 2) {
			#print_r($this->controllerNamespace);
			// 提取控制器名（首字母大写 + Controller 后缀）
			$controllerName = ucfirst($parts[1]) . 'Controller';
			$controllerFqcn = "{$this->controllerNamespace}\\{$controllerName}";
			
			// 提取动作名（第二个路径段）
			$actionName = $parts[2];
			
			// 检查控制器和动作是否存在
			if (class_exists($controllerFqcn) && method_exists($controllerFqcn, $actionName)) {
				// 提取后续参数（第三个及以后的路径段，如 ["1"] → $vars['id']=1）
				if (count($parts) > 2) {
					$params = array_slice($parts, 2);
					// 规则1：单个参数默认映射为 id（如 /user/show/1 → id=1）
					if (count($params) === 1) {
						$vars['id'] = $params[0];
					}
					// 规则2：多个参数按顺序映射为 param1, param2...（如 /user/search/1/admin → param1=1, param2=admin）
					else {
						foreach ($params as $key => $value) {
							$vars['param' . ($key + 1)] = $value;
						}
					}
				}
				return [$controllerFqcn, $actionName, $vars];
			}
		}

		// -------------- 原有逻辑：处理「控制器/参数」格式（兼容旧路由） --------------
		// 场景2：路径段 =1（如 ["user"] → 控制器=User, 动作=默认REST动作, 参数=无）
		for ($i = count($parts); $i >= 1; $i--) {
			$controllerSegments = array_slice($parts, 0, $i);
			$actionSegments = array_slice($parts, $i);

			$controllerName = $this->buildControllerClassName($controllerSegments);
			if (!class_exists($controllerName)) {
				continue;
			}

			// 确定默认动作（基于RESTful优先级）
			list($action, $actionVars) = $this->determineActionAndParams(
				$controllerName,
				$actionSegments,
				$method,
				$restPriority
			);

			if ($action !== null) {
				return [$controllerName, $action, $actionVars];
			}
		}

		// 未匹配到任何路由
		return [null, null, []];
	}

//------------------------------------------------------------------------------
	private function resolveAutoRoute1(string $path): array
	{
		$path = rtrim($path, '/');
		$parts = array_filter(explode('/', $path));
		$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
		$method = $request->getMethod();

		// 根路径直接映射到HomeController@index
		if ($path === '') {
		#	return ["{$this->controllerNamespace}HomeController", 'index', []];
		}
		// 根路径特殊处理 (这是关键的修复点)
		if ($path === '') {
			$controllerFqcn = "{$this->controllerNamespace}\\HomeController";
			// 检查 HomeController 是否存在以及 index 方法是否存在
			if (class_exists($controllerFqcn) && method_exists($controllerFqcn, 'index')) {
				return [$controllerFqcn, 'index', []];
			}
			// 如果 HomeController 不存在，则返回 404
			return [null, null, []];
		}

		// RESTful动作优先级映射
		$restPriority = [
			'GET'    => ['index', 'show', 'create', 'edit'],
			'POST'   => ['store', 'create'],
			'PUT'    => ['update', 'edit'],
			'DELETE' => ['destroy'],
		];

		// 从长到短尝试所有可能的控制器路径
		for ($i = count($parts); $i >= 1; $i--) {
			$controllerSegments = array_slice($parts, 0, $i);
			$actionSegments = array_slice($parts, $i);

			// 构建控制器类名
			$className = $this->buildControllerClassName($controllerSegments);
			if (!class_exists($className)) {
				continue; // 控制器不存在，尝试更短的路径
			}

			// 确定动作和参数
			list($action, $vars) = $this->determineActionAndParams(
				$className,
				$actionSegments,
				$method,
				$restPriority
			);

			if ($action !== null) {
				return [$className, $action, $vars];
			}
		}

		// 没有找到匹配的控制器/动作
		return [null, null, []];
	}


    /**
     * 构建控制器完整类名（支持多级命名空间）
     */
    private function buildControllerClassName(array $segments): string
    {
        if (empty($segments)) {
            return "{$this->controllerNamespace}\\HomeController";
        }

        // 最后一段添加 Controller 后缀（如 User → UserController）
        $lastSegment = array_pop($segments);
        $lastSegment .= 'Controller';
        $segments[] = $lastSegment;

        // 拼接命名空间（如 ['Admin', 'UserController'] → App\Controllers\Admin\UserController）
        return $this->controllerNamespace . '\\' . implode('\\', array_map('ucfirst', $segments));
    }
	
	private function determineActionAndParams(
		string $className,
		array $actionSegments,
		string $method,
		array $restPriority
	): array {
		$vars = [];
		$availableActions = get_class_methods($className);

		// 1. 尝试RESTful风格匹配
		if (!empty($restPriority[$method])) {
			foreach ($restPriority[$method] as $candidate) {
				if (in_array($candidate, $availableActions)) {
					// 检查是否有ID参数
					if (!empty($actionSegments) && is_numeric($actionSegments[0])) {
						$vars['id'] = $actionSegments[0];
						return [$candidate, $vars];
					}
					// 没有ID参数但动作存在
					if (empty($actionSegments)) {
						return [$candidate, $vars];
					}
				}
			}
		}

		// 2. 尝试直接匹配动作名
		if (!empty($actionSegments)) {
			$actionCandidate = $actionSegments[0];
			if (in_array($actionCandidate, $availableActions)) {
				// 收集参数
				for ($i = 1; $i < count($actionSegments); $i++) {
					$vars["param" . ($i)] = $actionSegments[$i];
				}
				return [$actionCandidate, $vars];
			}
		}

		// 3. 尝试默认动作
		if (in_array('index', $availableActions)) {
			return ['index', $vars];
		}

		return [null, []];
	}
//------------------------------------------------------------------------------

	/**
	 * 构建控制器完整类名（支持多级命名空间，如 ["admin", "user"] → App\Controllers\Admin\UserController）
	 */
	 //修复http://localhost:8000/home/show/666
	private function buildControllerClassName1(array $segments): string
	{
		if (empty($segments)) {
			return "{$this->controllerNamespace}\\HomeController";
		}

		// 最后一段添加 Controller 后缀，前面的段作为命名空间
		$lastSegment = array_pop($segments);
		$lastSegment .= 'Controller';
		$segments[] = $lastSegment;

		// 拼接命名空间（首字母大写，如 ["admin", "userController"] → Admin\UserController）
		$namespaceSegments = array_map('ucfirst', $segments);
		return $this->controllerNamespace . '\\' . implode('\\', $namespaceSegments);
	}


	/**
	 * 确定默认动作和参数（用于无显式动作的场景，如 /user/1 → UserController@show(id=1)）
	 */
	 //修复http://localhost:8000/home/show/666
	private function determineActionAndParams1(
		string $className,
		array $actionSegments,
		string $method,
		array $restPriority
	): array {
		$vars = [];
		$availableActions = get_class_methods($className);

		// 1. 基于RESTful优先级匹配默认动作（如 GET 请求 /user/1 → show 动作）
		if (!empty($restPriority[$method])) {
			foreach ($restPriority[$method] as $candidateAction) {
				if (in_array($candidateAction, $availableActions)) {
					// 提取参数（如 /user/1 → id=1）
					if (!empty($actionSegments) && is_numeric($actionSegments[0])) {
						$vars['id'] = $actionSegments[0];
					}
					return [$candidateAction, $vars];
				}
			}
		}

		// 2. 匹配显式动作（如 /user/edit/1 → edit 动作）
		if (!empty($actionSegments)) {
			$actionCandidate = $actionSegments[0];
			if (in_array($actionCandidate, $availableActions)) {
				// 提取后续参数（如 /user/edit/1/abc → param1=1, param2=abc）
				for ($i = 1; $i < count($actionSegments); $i++) {
					$vars["param" . $i] = $actionSegments[$i];
				}
				return [$actionCandidate, $vars];
			}
		}

		// 3. 默认动作：index（如 /user → index 动作）
		if (in_array('index', $availableActions)) {
			return ['index', $vars];
		}

		return [null, []];
	}



















	private function isLikelyMethod($str): bool
	{
		return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $str)
			&& !preg_match('/^[0-9]/', $str)
			&& !in_array(strtolower($str), ['php', 'html', 'css', 'js']);
	}

	private function methodExistsInAnyController($method): bool
	{
		// 简化：我们假设大部分控制器都有 index 方法
		return in_array($method, ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy', 'add', 'save']);
	}

	private function instantiateController_1(string $class)
	{
		if (class_exists($class)) {
			return new $class();
		}
		return null;
	}

    /**
     * 实例化控制器
     */
    private function instantiateController_DI(string $class)
    {
        if (class_exists($class)) {
            // 支持控制器构造函数依赖注入（如需简化，可直接 new $class()）
            $reflection = new \ReflectionClass($class);
            $constructor = $reflection->getConstructor();
            if ($constructor && $constructor->getNumberOfParameters() > 0) {
                // 此处可扩展依赖注入容器，当前简化为无参构造
                throw new \RuntimeException("Controller {$class} requires constructor parameters (DI not supported yet).");
            }
            return new $class();
        }
        return null;
    }

    /**
     * 提取方法参数（支持 URL 参数、GET/POST 参数）
     */
    private function extractMethodArguments1($controller, array $vars, Request $request)
    {
        $reflector = new \ReflectionMethod($controller[0], $controller[1]);
		
        $args = [];

        foreach ($reflector->getParameters() as $param) {
            $paramName = $param->getName();
            $paramValue = null;

            // 1. 优先从 URL 路径参数获取（如 /show/{id} → id=123）
            if ($request->attributes->has($paramName)) {
                $paramValue = $request->attributes->get($paramName);
            }
            // 2. 从 GET/POST 参数获取
            elseif ($request->get($paramName) !== null) {
                $paramValue = $request->get($paramName);
            }
            // 3. 从自动路由的 vars 中获取（如 param1=123）
            elseif (isset($vars[$paramName])) {
                $paramValue = $vars[$paramName];
            }
            // 4. 使用参数默认值
            elseif ($param->isDefaultValueAvailable()) {
                $paramValue = $param->getDefaultValue();
            }

            // 检查参数是否必填
            if ($paramValue === null && !$param->isOptional()) {
                throw new \InvalidArgumentException("Missing required parameter: {$paramName}");
            }

            // 类型转换（匹配参数声明类型）
            $paramType = $param->getType();
            if ($paramType && !$paramType->isBuiltin()) {
                // 支持简单的类类型提示（如需复杂 DI，需扩展容器）
                $paramClass = $paramType->getName();
                if (class_exists($paramClass) && $paramClass === Request::class) {
                    $paramValue = $request;
                }
            } elseif ($paramType) {
                // 基础类型转换（如 int、string）
                $type = $paramType->getName();
                settype($paramValue, $type);
            }

            $args[] = $paramValue;
        }

        return $args;
    }

	// framework/Core/Router.php
	private function extractMethodArguments($controller, array $vars, Request $request)
	{
        $reflector = new \ReflectionMethod($controller[0], $controller[1]);
        $args = [];

		foreach ($reflector->getParameters() as $param) {
			$paramName = $param->getName();
			$paramValue = null;
			
			// 优先从URL路径参数获取
			if ($request->attributes->has($paramName)) {
				$paramValue = $request->attributes->get($paramName);
			}
			// 注入Request对象
			elseif ($param->getType() && $param->getType()->getName() === Request::class) {
				$paramValue = $request;
			}
			// 从GET/POST参数获取
			elseif ($request->get($paramName) !== null) {
				$paramValue = $request->get($paramName);
			}
			// 从自动路由vars获取
			elseif (isset($vars[$paramName])) {
				$paramValue = $vars[$paramName];
			}
			// 使用默认值
			elseif ($param->isDefaultValueAvailable()) {
				$paramValue = $param->getDefaultValue();
			}
			
			// 处理缺失参数
			if ($paramValue === null && !$param->isOptional()) {
				// 在生产环境不暴露详细错误
				if (getenv('APP_ENV') === 'production') {
					throw new HttpException(400, 'Bad Request');
				} else {
					throw new \InvalidArgumentException("Missing parameter: {$paramName}");
				}
			}
			

            // 类型转换（匹配参数声明类型）
            $paramType = $param->getType();
            if ($paramType && !$paramType->isBuiltin()) {
                // 支持简单的类类型提示（如需复杂 DI，需扩展容器）
                $paramClass = $paramType->getName();
                if (class_exists($paramClass) && $paramClass === Request::class) {
                    $paramValue = $request;
                }
            } elseif ($paramType) {
                // 基础类型转换（如 int、string）
                $type = $paramType->getName();
                settype($paramValue, $type);
            }
			
			
			// 类型转换...
			
			$args[] = $paramValue;
		}
		
		return $args;
	}



	private function extractMethodArguments0($controller, array $vars, Request $request)
	{
		$reflector = new \ReflectionMethod($controller[0], $controller[1]);
		$args = [];

		foreach ($reflector->getParameters() as $param) {
			$name = $param->getName();
			$value = $request->attributes->get($name) ?: $request->get($name);

			// 从 $vars 中找 param1, param2...
			if (!$value) {
				foreach ($vars as $k => $v) {
					if ($k === $name || preg_match('/param\d+/', $k)) {
						$value = $v;
						break;
					}
				}
			}

			if ($value === null && $param->isDefaultValueAvailable()) {
				$value = $param->getDefaultValue();
			}

			if ($value === null && !$param->isOptional()) {
				throw new \InvalidArgumentException("Missing parameter: $name");
			}

			$args[] = $value;
		}

		return $args;
	}
	
	
	/**
	 * 应用中间件
	 */
	private function applyMiddleware(Request $request)
	{
		// 创建中间件实例
		$methodOverride = new MethodOverrideMiddleware();

		// 构建中间件栈（可扩展）
		$middlewareChain = function ($req) {
			// 这是链条的末端，返回一个空响应，实际会被后续的控制器响应覆盖
			return new \Symfony\Component\HttpFoundation\Response();
		};

		// 执行中间件
		$methodOverride->handle($request, $middlewareChain);
	}
	
	

	/**
	 * 从 DI 容器获取控制器实例
	 * @param string $class 控制器的完整类名 (Fully Qualified Class Name)
	 * @return object 控制器实例
	 * @throws \RuntimeException 如果控制器在容器中未找到
	 */
	private function instantiateController(string $class)
	{
		// 为了调试，我们先打印出要查找的类名
		//var_dump("正在尝试从容器中获取控制器: " . $class);
		//var_dump("查找控制器:", $class);
		//var_dump("容器类型:", get_class($this->container)); // 应该是 Framework\Container\Container

		// 检查容器是否注入成功
		if ($this->container === null) {
			throw new \RuntimeException("容器未注入到 Router 中！");
		}

		// 检查容器中是否存在该服务
		if ($this->container->has($class)) {
			// 从容器中获取服务
			$controller = $this->container->get($class);

			//var_dump("成功从容器中获取到对象: ", $controller);  //没有执行到这里
			
			return $controller;
		}
		
		
        // 方式二：直接使用静态容器（如果不想修改Router构造函数，可以用这个）
        // if (StaticContainer::has($class)) {
        //     return StaticContainer::get($class);
        // }
			
		
        if (class_exists($class)) {
            // 支持控制器构造函数依赖注入（如需简化，可直接 new $class()）
            $reflection = new \ReflectionClass($class);
            $constructor = $reflection->getConstructor();
            if ($constructor && $constructor->getNumberOfParameters() > 0) {
                // 此处可扩展依赖注入容器，当前简化为无参构造
                throw new \RuntimeException("Controller {$class} requires constructor parameters (DI not supported yet).");
            }
            return new $class();
        }
        return null;
		

		// 如果代码执行到这里，说明容器里没有这个控制器
		throw new \RuntimeException(
			"Controller '{$class}' not found in the dependency injection container. " .
			"Did you forget to register it in config/services.php?"
		);
	}




	
}