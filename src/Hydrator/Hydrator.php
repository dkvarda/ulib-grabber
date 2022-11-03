<?php

namespace Ulib\Grabber\Hydrator;

use Ulib\Grabber\Entity\IEntity;
use ReflectionClass;

class Hydrator
{
    public function patch(IEntity $entity, array $data): IEntity
    {
        $reflectionEntity = new ReflectionClass($entity);
        foreach ($reflectionEntity->getProperties() as $property) {
            $namespace = $reflectionEntity->getNamespaceName();
            $property->setAccessible(true);
            if (key_exists($property->getName(), $data)) {
                $value = $data[$property->getName()];
                if (is_array($value)) {
                    $class = ucfirst($property->getName());
                    $class = $namespace . '\\' . $class;
                    $value = self::patch(new $class, $value);
                }
                $property->setValue($entity, $value);
            }
        }
        return $entity;
    }
}
