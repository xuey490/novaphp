<?php
namespace Framework\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 * @Attributes({
 *     @Attribute("path", type = "string"),
 *     @Attribute("name", type = "string"),
 *     @Attribute("defaults", type = "array"),
 *     @Attribute("requirements", type = "array")
 * })
 */
class Delete extends Route
{
    /**
     * 构造函数：默认设置请求方法为GET
     */
    public function __construct(array $values)
    {
        #parent::__construct();
        $this->methods = ['DELETE'];
        
        // 处理注解参数
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}