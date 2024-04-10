<?php

namespace Diana\Support\Helpers;

use ReflectionNamedType;
use ReflectionParameter;

class Util
{
    /**
     * Get the class name of the given parameter's type, if possible.
     *
     * From Reflector::getParameterClassName() in Illuminate\Support.
     */
    public static function getParameterClassName(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }

    /**
     * Get the class names of the given parameter's type, including union types.
     *
     * @param  \ReflectionParameter  $parameter
     * @return array
     */
    public static function getParameterClassNames($parameter)
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionUnionType) {
            return array_filter([static::getParameterClassName($parameter)]);
        }

        $unionTypes = [];

        foreach ($type->getTypes() as $listedType) {
            if (!$listedType instanceof ReflectionNamedType || $listedType->isBuiltin()) {
                continue;
            }

            $unionTypes[] = static::getTypeName($parameter, $listedType);
        }

        return array_filter($unionTypes);
    }

    /**
     * Get the given type's class name.
     *
     * @param  \ReflectionParameter  $parameter
     * @param  \ReflectionNamedType  $type
     * @return string
     */
    protected static function getTypeName($parameter, $type)
    {
        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }
}