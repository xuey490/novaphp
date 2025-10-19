![Static Badge](https://img.shields.io/badge/%3E%3Dphp-8.1-green)    ![Static Badge](https://img.shields.io/badge/MIT-License-blue)  ![Static Badge](https://img.shields.io/badge/Symfony_7-green)     [![zread](https://img.shields.io/badge/Ask_Zread-_.svg?style=flat-square&color=00b0aa&labelColor=000000&logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQuOTYxNTYgMS42MDAxSDIuMjQxNTZDMS44ODgxIDEuNjAwMSAxLjYwMTU2IDEuODg2NjQgMS42MDE1NiAyLjI0MDFWNC45NjAxQzEuNjAxNTYgNS4zMTM1NiAxLjg4ODEgNS42MDAxIDIuMjQxNTYgNS42MDAxSDQuOTYxNTZDNS4zMTUwMiA1LjYwMDEgNS42MDE1NiA1LjMxMzU2IDUuNjAxNTYgNC45NjAxVjIuMjQwMUM1LjYwMTU2IDEuODg2NjQgNS4zMTUwMiAxLjYwMDEgNC45NjE1NiAxLjYwMDFaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00Ljk2MTU2IDEwLjM5OTlIMi4yNDE1NkMxLjg4ODEgMTAuMzk5OSAxLjYwMTU2IDEwLjY4NjQgMS42MDE1NiAxMS4wMzk5VjEzLjc1OTlDMS42MDE1NiAxNC4xMTM0IDEuODg4MSAxNC4zOTk5IDIuMjQxNTYgMTQuMzk5OUg0Ljk2MTU2QzUuMzE1MDIgMTQuMzk5OSA1LjYwMTU2IDE0LjExMzQgNS42MDE1NiAxMy43NTk5VjExLjAzOTlDNS42MDE1NiAxMC42ODY0IDUuMzE1MDIgMTAuMzk5OSA0Ljk2MTU2IDEwLjM5OTlaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik0xMy43NTg0IDEuNjAwMUgxMS4wMzg0QzEwLjY4NSAxLjYwMDEgMTAuMzk4NCAxLjg4NjY0IDEwLjM5ODQgMi4yNDAxVjQuOTYwMUMxMC4zOTg0IDUuMzEzNTYgMTAuNjg1IDUuNjAwMSAxMS4wMzg0IDUuNjAwMUgxMy43NTg0QzE0LjExMTkgNS42MDAxIDE0LjM5ODQgNS4zMTM1NiAxNC4zOTg0IDQuOTYwMVYyLjI0MDFDMTQuMzk4NCAxLjg4NjY0IDE0LjExMTkgMS42MDAxIDEzLjc1ODQgMS42MDAxWiIgZmlsbD0iI2ZmZiIvPgo8cGF0aCBkPSJNNCAxMkwxMiA0TDQgMTJaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00IDEyTDEyIDQiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K&logoColor=ffffff)](https://zread.ai/xuey490/novaphp)
  

## 简介:
这是一款基于symfony的底层代码开发的轻量级，强大，快速，简单，安全的php框架。

## 核心特性
🚀 性能与安全
- 	Symfony 组件集成：利用 Symfony 7.x 组件实现 HTTP 基础、路由、依赖注入和缓存
- 	轻量级设计：最小开销，快速请求处理
- 	内置安全：CSRF保护、XSS过滤、速率限制和 IP阻断等中间件
- 	路由缓存：基于文件的缓存系统优化路由加载
- 	日志检查：基于Monolog的强大的日志功能
  
🔧 开发体验
- 	多种路由选项：支持手动路由配置和基于属性的路由
- 	依赖注入：完整的 Symfony DI 容器集成用于服务管理
- 	模板灵活性：双重模板引擎支持（Twig 和 ThinkTemplate）
- 	ORM 集成：ThinkORM 集成用于数据库操作
- 	数据验证：ThinkValidate 的强大数据集验证
  
🛠️ 现代 PHP 特性
- 	PHP 8.3+ 支持：利用现代 PHP 特性，包括属性和联合类型
- 	PSR 标准：遵循 PSR-4 自动加载和其他相关标准
- 	Composer 就绪：基于 Composer 的标准依赖管理

## 下载安装:
- 本地环境:php8.1及以上，Redis，MySQL5.7, composer 2.x及以上
- 从GitHub下载main版本，解压到本地目录，在根目录下运行composer install ,
- 等组件包下载完毕后，运行php -S localhost:8000 -t public
- 打开浏览器，输入地址http://localhost:8000
- 也可以部署到任何可以运行php的Apache或Nginx服务器上。

## 应用文档
强烈推荐Zread.Ai，感谢他们提供项目文档分析生成。https://zread.ai/xuey490/project

## 测试路由:
所有的控制器，都在App/controllers, 按http://localhost:8000/控制器名/动作名  访问，如下面

http://localhost:8000/user/add


## 版本里程: 更多见：https://github.com/xuey490/novaphp/blob/main/version.md

- 0.1.2
	- 增加topthink/think-validate验证器，轻量级vlucas/valitron
    - 引入phpCSfixer对全部代码进行修订，100%兼容psr-12
    - 其它细节修改


- 0.1.1
	- 遗弃thinkcache
	- 遗弃doctrine/annotations，改成Symfony 路由写法的 Attribute 路由定义类
	- 在Symfony 注解路由上实现中间件加载
	- 实现验证码,并完成控制器编写测试
	- 改写了ThinkTemp的扩展
	- app下的测试文件修改
    - 增加命令行生成控制器，模型，中间件 
	- 生成控制器 #php novaphp make:controller User # 生成模型#php novaphp make:model User # 生成Auth中间件 #php novaphp make:middleware Auth
	- 其它细节修改


- 0.1.0
	- 增加文件上传组件，并完成控制器编写测试，http://localhost:8000/upload/form

- 0.0.10
	- 引入Twig模板引擎，完成模板引擎注册服务，扩展，演示以及模板 http://localhost:8000/blog/ http://localhost:8000/view
	- 重写了熔断器和csrf中间件，增加了referer来路检测中间件
	- 引入symfony/cache重写缓存组件，放弃兼容psr-16，下一版本可能放弃thinkcache ，目前仍可用app('cache')->set/get
	- 优化路由，如不存在路由，自动跳转到404页面
	- 优化错误显示的页面
	- 修改核心文件Framework.php 去除PSR-7的兼容
	- 修改日志类，去除PSR-7的兼容
	- 增加twig解析markdown的功能
	- 引入thinkphp的模板引擎，可以自动同时使用两个模板引擎
> 0.0.10是一个里程碑的版本，已经基本具备所有现代php框架的特性。


**祝您使用愉快！**

## 彩蛋
打开浏览器，输入地址http://localhost:8000/version ,
http://localhost:8000/team

