<?php

namespace Framework\Core;

class Container
{
    protected $bindings = [];

    public function bind($abstract, $concrete = null)
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    public function make($abstract)
    {
        $concrete = $this->bindings[$abstract] ?? $abstract;

        if ($concrete === $abstract && !is_object($concrete)) {
            $reflector = new \ReflectionClass($concrete);
            $constructor = $reflector->getConstructor();

            if (!$constructor) {
                return new $concrete;
            }

            $parameters = $constructor->getParameters();
            $dependencies = array_map(function ($param) {
                $type = $param->getType();
                if (!$type || $type->isBuiltin()) {
                    throw new \Exception("Cannot resolve parameter: {$param->getName()}");
                }
                return $this->make($type->getName());
            }, $parameters);

            return $reflector->newInstanceArgs($dependencies);
        }

        return is_callable($concrete) ? $concrete($this) : new $concrete;
    }
}