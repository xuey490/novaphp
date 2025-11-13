Framework\Container\Container 类设计思路是围绕 Symfony DI 容器构建一个统一入口类，既负责：

- .env 环境加载；
- services.php 服务定义文件加载；
- 生产环境容器编译缓存；
- 对 ContainerInterface 的代理封装。
- 兼容psr-13的接口

Container.php 内部逻辑示意图 + 初始化流程图，用文字图（ASCII 风格）和流程描述，让整个 compile / cache / prod/dev 逻辑一目了然

1️⃣ Container.php 内部逻辑示意图（单文件概览）

```cpp
Container (implements Symfony ContainerInterface)
├─ private static $container : SymfonyContainerInterface|null
├─ private const CACHE_FILE = BASE_PATH.'/storage/cache/container.php'
├─ public static init(array $parameters = [])
│   ├─ 如果 self::$container 已存在 -> return
│   ├─ 加载 .env
│   ├─ 获取 APP_ENV, 判断是否生产环境 $isProd
│   ├─ 确认 config 目录存在
│   ├─ 确认 services.php 文件存在
│   ├─ 创建 ContainerBuilder
│   │   ├─ 设置 kernel.project_dir
│   │   ├─ 设置 kernel.debug
│   │   └─ 设置 kernel.environment
│   ├─ 如果 $parameters 不为空 -> 注入 'config' 参数
│   ├─ 加载服务文件 PhpFileLoader(services.php)
│   ├─ $containerBuilder->compile()
│   ├─ 如果 $isProd (生产环境)
│   │   ├─ mkdir cache 目录
│   │   ├─ PhpDumper dump -> cacheContent
│   │   ├─ 写入 CACHE_FILE
│   │   └─ require CACHE_FILE -> self::$container
│   └─ 否则 (开发环境)
│       └─ self::$container = $containerBuilder
├─ public static getInstance(): self
│   └─ 调用 init() -> return new self()
├─ PSR-11 代理方法
│   ├─ get($id)
│   ├─ has($id)
│   ├─ getParameter($name)
│   └─ setParameter($name, $value)

```


2️⃣ Container 初始化流程图（ASCII 风格）

```cpp
+-------------------------+
|  Container::init()      |
+-------------------------+
            |
            v
+-------------------------+
| 已有容器? self::$container != null
+-------------------------+
            | Yes
            v
       return existing
            |
           No
            v
+-------------------------+
| 加载 .env 文件          |
+-------------------------+
            |
            v
+-------------------------+
| 读取 APP_ENV, APP_DEBUG  |
| 判断 $isProd            |
+-------------------------+
            |
            v
+-------------------------+
| 检查 config/ 目录和     |
| services.php 文件       |
+-------------------------+
            |
            v
+-------------------------+
| 创建 ContainerBuilder   |
| 设置参数 kernel.*       |
| 注入全局参数 $parameters|
+-------------------------+
            |
            v
+-------------------------+
| 加载服务文件 services.php|
+-------------------------+
            |
            v
+-------------------------+
| $containerBuilder->compile() |
+-------------------------+
            |
            v
+-------------------------+
| 生产环境? $isProd        |
+-------------------------+
   | Yes                   | No
   v                       v
+-------------------+   +-------------------+
| 生成 PhpDumper    |   | 使用 ContainerBuilder |
| dump -> cache.php  |   | 直接作为 self::$container |
| require cache.php  |   +-------------------+
| self::$container=loaded
+-------------------+

```

3️⃣ 核心思路总结

单例静态容器：self::$container

开发环境：每次都重新构建，保证修改立即生效

生产环境：生成编译缓存文件，避免每次构建

PSR-11 兼容：提供 get() / has() 方法

参数注入：kernel.* + 全局 config

服务加载：services.php + autowire + public + args

最终容器可通过：

Container::getInstance() 获取容器对象

App::setContainer(Container::getInstance()) 注入全局

helpers: app('service_id') / getService(ClassName::class)

===================================================

下面我画一张直观的 Provider 注册 & boot 顺序图，展示现在框架的流程，包括核心 Provider 和应用 Provider，以及 register() 和 boot() 阶段。为了清晰，我用文本流程图的形式描述
```
+------------------------+
| Container 初始化        |
|  ContainerConfigurator |
+------------------------+
            |
            v
+------------------------+
| 核心 Provider 注册阶段  |
| 读取 config/providers.php |
+------------------------+
            |
            v
+------------------------+
| 逐个 register() 调用   |
|  RequestProvider       |
|  ResponseProvider      |
|  SessionServiceProvider|
|  CookieServiceProvider |
|  MiddlewaresProvider   |
|  ConfigServiceProvider |
|  LoggerServiceProvider |
|  ...                   |
+------------------------+
            |
            v
+------------------------+
| 应用 Provider 注册阶段  |
| 扫描 app/Providers     |
| 自动 register()        |
+------------------------+
            |
            v
+------------------------+
| 所有 Provider boot()   |
|  按 loadedProviders 顺序|
| 核心 -> 应用 Provider   |
+------------------------+
            |
            v
+------------------------+
| 容器编译完成            |
|  可以安全获取任意服务   |
+------------------------+
```

说明

register() 阶段

只是注册服务定义到容器

不会触发服务实例化（延迟到容器编译时）

核心 Framework\Provider 应该先注册，保证 App Provider 可以引用它们的服务

boot() 阶段

触发 Provider 内的初始化逻辑（比如事件监听器、Session 启动）

顺序按 loadedProviders 数组，也就是先注册的先 boot()

如果某个 Provider 依赖其他 Provider 的初始化结果，必须确保它在依赖 Provider 之后注册

容器使用

ContainerConfigurator 会在 Framework\Container\Container 的 compile 阶段解析依赖

Autowire 会自动注入构造函数所需的类型

如果服务在 register() 时未定义，会报错.