![Static Badge](https://img.shields.io/badge/%3E%3Dphp-8.1-green)    ![Static Badge](https://img.shields.io/badge/MIT-License-blue)  ![Static Badge](https://img.shields.io/badge/Symfony_7-green)


## 简介:
这是一款基于symfony的底层代码开发的轻量级，强大，快速，简单，安全的php框架。

## 下载安装:
- 本地环境:php8.1及以上，Redis，MySQL5.7及以上
-  从GitHub下载main版本，解压到本地目录，在根目录下运行php -S localhost:8000 -t public
- 打开浏览器，输入地址http://localhost:8000

## 彩蛋
打开浏览器，输入地址http://localhost:8000/version ,
http://localhost:8000/team


## 测试路由:
所有的控制器，都在App/controllers, 按http://localhost:8000/控制器名/动作名  访问，如下面

http://localhost:8000/user/add


## 版本里程: 更多见
https://github.com/xuey490/novaphp/blob/main/version.md
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
