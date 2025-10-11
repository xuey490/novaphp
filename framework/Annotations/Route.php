<?php

namespace Framework\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Route
{
    /**
     * 路由路径
     * @var string
     */
    public $path;

    /**
     * HTTP请求方法（GET/POST/PUT/DELETE）
     * @var string|array
     */
    public $methods = ['GET' , 'POST' ,'PUT' ,'DELETE'];

    /**
     * 路由名称（可选）
     * @var string
     */
    public $name = '';

    /**
     * 路由参数默认值（可选）
     * @var array
     */
    public $defaults = [];

    /**
     * 路由参数正则约束（可选）
     * @var array
     */
    public $requirements = [];

    /*
    * 路由参数默认值（可选）
    */
    public $options = []; // 必须添加这个属性！


    public function __construct(array $values)
    {
        $this->path = $values['path'] ?? '';
        $this->name = $values['name'] ?? '';
        $this->methods = $values['methods'] ?? [];
        $this->defaults = $values['defaults'] ?? [];
        $this->requirements = $values['requirements'] ?? [];
        $this->options = $values['options'] ?? []; // 初始化options
    }

}
