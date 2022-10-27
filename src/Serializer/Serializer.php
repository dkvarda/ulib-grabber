<?php

namespace Ulib\Grabber\Serializer;

use ReflectionClass;
use ReflectionException;

final class Serializer
{
    /**
     * @throws ReflectionException
     */
    public static function array(array $data): array
    {
        $out = [];
        foreach ($data as $item) {
            $oneOut = [];
            $class = new ReflectionClass($item);
            $properties = [];
            foreach ($class->getProperties() as $value) {
                if (self::doNotSerialize($value) === false) { // skip manually ignored properties based on annotations
                    continue;
                }
                if ($value->getDeclaringClass()->getName() === get_class($item)) {
                    $value->setAccessible(true);
                    $oneOut[$value->getName()] = $value->getValue($item);
                    $properties[] = $value->getName();
                }
            }
            foreach ($class->getMethods() as $value) {
                if ($value->getDeclaringClass()->getName() !== get_class($item)) { // do not serialize parent classes
                    continue;
                }
                if (strpos($value->getName(), 'set') !== false) { // skip setters
                    continue;
                }
                if (self::doNotSerialize($value) === false) { // skip manually ignored methods based on annotations
                    continue;
                }
                $name = lcfirst(explode('get', $value->getName())[1]);
                if ($class->hasProperty($name) && !in_array($name, $properties)) {
                    continue;
                }
                $oneOut[$name] = $item->{$value->getName()}();
            }
            $out[] = $oneOut;
        }
        return $out;
    }

    /**
     * @param $object
     * @return false|string
     * @throws ReflectionException
     */
    public static function json($object)
    {
        return json_encode(self::array($object));
    }

    private static function doNotSerialize($object): bool
    {
        if ($object->getDocComment() === false || strpos('@ignore', $object->getDocComment()) !== false) {
            return true;
        }
        return false;
    }
}
