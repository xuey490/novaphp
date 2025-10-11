<?php
// src/Factory/ThinkTemplateFactory.php

namespace App\twig;


class ThinkTemplateFactory
{
    public function __invoke(array $config)
    {
        // 这个 __invoke 方法将被 Symfony 调用
        // $config 参数会自动从服务定义中注入

        // 1. 创建并返回模板引擎实例
        $template = new \think\Template($config);


        // 2. 在这里“注入”您的自定义函数
        // 我们使用 assign 方法，这是所有版本都支持的
        $template->assign([
            'hello' => 'tpTemplateHello', // '模板中使用的名称' => 'PHP中的函数名'
            'formatDate' => 'tpTemplateFormatDate',
        ]);

        // 3. (可选) 在这里可以进行任何额外的初始化
        // 例如注册自定义函数、标签库等
        // $template->registerFunction('my_func', 'App\Helper\MyHelper::myFunc');
        
        return $template;
    }
}