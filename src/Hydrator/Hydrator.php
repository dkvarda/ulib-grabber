<?php

declare(strict_types=1);

namespace Ulib\Grabber\Hydrator;

use ReflectionClass;
use Ulib\Grabber\Entity\IEntity;

class Hydrator
{
    public function patch(IEntity $entity, array $data): IEntity
    {
        $reflectionEntity = new ReflectionClass($entity);

        foreach ($reflectionEntity->getProperties() as $property) {
            $namespace = $reflectionEntity->getNamespaceName();
            $property->setAccessible(true);

            $propertyName = $property->getName();
            if (!array_key_exists($propertyName, $data)) {
                continue;
            }

            $value = $data[$propertyName];
            if (is_array($value)) {
                $class = $namespace . '\\' . ucfirst($propertyName);
                if (class_exists($class)) {
                    $value = $this->patch(new $class(), $value);
                }
            }

            $property->setValue($entity, $value);
        }

        return $entity;
    }
}
