<?php

declare(strict_types=1);

namespace App\Util;

class ReflectionUtil
{
    /**
     * @throws \ReflectionException
     */
    public static function getPropertyValue(object $object, string $propertyName): mixed
    {
        return (new \ReflectionProperty($object, $propertyName))->getValue($object);
    }

    /**
     * @throws \ReflectionException
     */
    public static function setPropertyValue(object $object, string $propertyName, mixed $value): void
    {
        (new \ReflectionProperty($object, $propertyName))->setValue($object, $value);
    }
}
