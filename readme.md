
##简介:
这是一款基于symfony的底层代码开发的轻量级，强大，快速，简单，安全的php框架。

##下载安装:
- 本地环境:php8.1及以上，Redis，MySQL5.7及以上
-  从GitHub下载main版本，解压到本地目录，在根目录下运行php -S localhost:8000 -t public
- 打开浏览器，输入地址http://localhost:8000

## 彩蛋
打开浏览器，输入地址http://localhost:8000/version ,
http://localhost:8000/team

## 测试路由:
所有的控制器，都在App/controllers, 按http://localhost:8000/控制器名/动作名  访问，如下面

http://localhost:8000/user/add


## 版本里程:
- 0.0.1 基础框架搭建，完成核心路由转发

- 0.0.2 实现日志，DI注册依赖注入，完善路由

- 0.0.3 实现中间件，注册服务

- 0.0.4 实现注解路由，应用层支持多个中间件

- 0.0.5 完成ORM，整合了ThinkORM，模型完全兼容Thinkphp特性

- 0.0.6 完成：
	- 容器管理, 重写Container（容器）改用symfony的容器，兼容PSR-11
	- 容器可以直接注册服务，也可以注册具体的业务操作类，增加了助手函数
	- 完成配置管理，增加了配置服务注册（可选）
	- 完成日志注册服务；
- 0.0.7 完成:
	-  重写日志服务，对日志分类，大小，归档等；
	-  异常服务，处理，友好错误显示，增加request-ID便于追踪堆栈
	-  修改核心文件Framework.php 增加Symfony Request 与PSR-7的兼容
	-  增加了Kernel核心文件，增加app()助手函数， 获取服务容器或解析服务
- 0.0.8 完成
	- 引入thinkcache缓存类库，实现缓存服务
	- 引入phpunit，生成测试类php phpunit.php， vendor\bin\phpunit
	- 增加了版本彩蛋（纯粹无聊）http://localhost:8000/version 可以访问彩蛋
	- 增加了i18n多国语言环境，自动检测载入语言包，http://localhost:8000/?lang=en/zh_CN/zh_TW/ja 切换
	- 0.0.9 完成
	- 重写中间件，添加多个全局变量Cors，RateLimiter（限流器）、CircuitBreaker（熔断器）、XSS过滤，IP Block（IP拦截）等
	- 注册session服务，支持redis/file切换
- 0.0.10
	- 引入Twig模板引擎，完成模板引擎注册服务，扩展，演示以及模板 http://localhost:8000/blog/ http://localhost:8000/view
	- 重写了熔断器和csrf中间件，增加了referer来路检测中间件
	- 引入symfony/cache重写缓存组件，放弃兼容psr-16，下一版本可能放弃thinkcache ，目前仍可用app('cache')->set/get
	- 优化路由，如不存在路由，自动跳转到404页面
	- 优化错误显示的页面
	- 修改核心文件Framework.php 去除PSR-7的兼容
	- 修改日志类，去除PSR-7的兼容
> 0.0.10是一个里程碑的版本，已经基本具备所有现代php框架的特性。


**祝您使用愉快！**
