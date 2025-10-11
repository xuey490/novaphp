<?php

// 文件路径: /Framework/Annotations/Middleware.php

namespace Framework\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Middleware
{
    /**
     * The middleware service class name.
     *
     * @var string
     */
    public $class;

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->class = $values['value'];
        } elseif (isset($values['class'])) {
            $this->class = $values['class'];
        }
    }
}
