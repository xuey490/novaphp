<?php
namespace Framework\Annotations;

use Doctrine\Common\Annotations\Annotation;
use Attribute; // 引入 Attribute

/**
 * @Annotation
 * @Target("METHOD")
 * @Attributes({
 *     @Attribute("path", type = "string"),
 *     @Attribute("name", type = "string"),
 *     @Attribute("defaults", type = "array"),
 *     @Attribute("requirements", type = "array"),
 *     @Attribute("options", type = "array")
 *     
 * })
 */
class Get extends Route
{
    /**
     * 构造函数：默认设置请求方法为GET
     */
    public function __construct(array $values)
    {
        #parent::__construct();
        $this->methods = ['GET'];
        
        // 处理注解参数
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}