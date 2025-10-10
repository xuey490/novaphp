V 0.0.1 初始化版本

需求：搭建一个 现代、模块化、符合 PSR 标准（PSR-4 自动加载）、运行于 PHP 8+ 的轻量级 PHP 框架

框架设计目标
遵循 PSR-4 自动加载规范
使用 Composer 管理依赖和自动加载
入口文件 public/index.php
应用目录结构清晰：App/Controller, App/Model, App/View
核心框架位于 framework/
配置集中管理在 config/
支持第三方组件：
路由：nikic/FastRoute
ORM：topthink/think-orm
使用工厂模式、接口抽象解耦核心组件
支持日志、缓存、上传、数据库等配置
MVC 架构清晰


总结：特性一览
特性	实现
PSR-4 自动加载	Composer + 命名空间
MVC 结构	App/Controller, Model, View
路由	FastRoute 封装
ORM	ThinkORM 集成
依赖注入	简易 IoC 容器
配置中心	config/*.php
日志系统	自定义 Logger
错误处理	统一异常捕获
扩展性	工厂、接口预留


V 0.0.2 需求：
路由配置繁琐 → 我们可以通过「约定优于配置」（Convention over Configuration）来实现自动路由。
安全防护缺失 → 需要在 Request 和 Response 中加入 XSS、CSRF 等基础安全机制


版本改进

总结：改进后特性
功能	实现
自动路由	基于 URL 路径自动映射控制器/方法，无需配置
命名约定	UserController → /user, store() → POST /user
XSS 防护	Request 构造时自动 htmlspecialchars 过滤输入
安全输出	Response 提供 json() 和默认编码头
CSRF 防护	提供 Token 生成与验证工具类
开发者友好	提供 raw() 获取原始数据，tokenField() 快速嵌入


V0.0.3 需求：混合路由模式 + 友好 URL（.html）+ 路由缓存 + CSRF 中间件化。我们将在现有框架基础上，充分利用 FastRoute 的高级功能，实现一个现代、高性能、安全的 PHP 框架路由系统。