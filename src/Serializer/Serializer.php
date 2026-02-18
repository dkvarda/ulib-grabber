<?php

declare(strict_types=1);

namespace Ulib\Grabber\Serializer;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

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
            $serializedProperties = [];

            foreach ($class->getProperties() as $property) {
                if (!self::shouldSerialize($property)) {
                    continue;
                }

                if ($property->getDeclaringClass()->getName() !== $class->getName()) {
                    continue;
                }

                $property->setAccessible(true);
                $name = $property->getName();
                $oneOut[$name] = $property->isInitialized($item) ? $property->getValue($item) : null;
                $serializedProperties[] = $name;
            }

            foreach ($class->getMethods() as $method) {
                if (!self::canSerializeMethod($method, $class->getName())) {
                    continue;
                }

                if (!self::shouldSerialize($method)) {
                    continue;
                }

                $name = self::methodOutputName($method->getName());
                if ($name !== null && $class->hasProperty($name) && in_array($name, $serializedProperties, true)) {
                    continue;
                }

                $outputName = $name ?? $method->getName();
                $oneOut[$outputName] = $item->{$method->getName()}();
            }

            $out[] = $oneOut;
        }

        return $out;
    }

    /**
     * @throws ReflectionException
     */
    public static function json(array $object): string|false
    {
        return json_encode(self::array($object));
    }

    private static function shouldSerialize(ReflectionProperty|ReflectionMethod $object): bool
    {
        $docComment = $object->getDocComment();

        return $docComment === false || strpos($docComment, '@ignore') === false;
    }

    private static function canSerializeMethod(ReflectionMethod $method, string $className): bool
    {
        if ($method->getDeclaringClass()->getName() !== $className) {
            return false;
        }

        if ($method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
            return false;
        }

        $name = $method->getName();
        if (str_starts_with($name, 'set')) {
            return false;
        }

        return str_starts_with($name, 'get') || str_starts_with($name, 'is') || str_starts_with($name, 'has');
    }

    private static function methodOutputName(string $methodName): ?string
    {
        if (str_starts_with($methodName, 'get')) {
            return lcfirst(substr($methodName, 3));
        }

        if (str_starts_with($methodName, 'is')) {
            return lcfirst(substr($methodName, 2));
        }

        if (str_starts_with($methodName, 'has')) {
            return lcfirst(substr($methodName, 3));
        }

        return null;
    }
}
