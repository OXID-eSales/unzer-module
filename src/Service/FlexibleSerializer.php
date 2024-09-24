<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use Exception;
use ReflectionClass;
use ReflectionException;
use stdClass;

class FlexibleSerializer
{
    protected Translator $translator;

    public function __construct(
        Translator $translator
    ) {
        $this->translator = $translator;
    }

    /**
     * Safely serialize an object
     *
     * @param mixed $object The object to serialize
     * @return string The serialized string
     */
    public function safeSerialize($object): string
    {
        $serializable = $this->makeSerializable($object);
        return serialize($serializable);
    }

    /**
     * Safely unserialize a string with optional allowed classes
     *
     * @param string $serialized The serialized string
     * @param array $allowedClasses An array of allowed class names
     * @return mixed The unserialized data
     */
    public function safeUnserialize(string $serialized, array $allowedClasses = [])
    {
        $unserializedData = unserialize(
            $serialized,
            [
                'allowed_classes' => array_merge(
                    $allowedClasses,
                    [stdClass::class]
                )
            ]
        );
        return $this->restoreUnserializable($unserializedData, $allowedClasses);
    }

    /**
     * Make an object or array serializable
     *
     * @param mixed $var The variable to make serializable
     * @return mixed The serializable version of the variable
     */
    private function makeSerializable($var)
    {
        if (is_object($var)) {
            $serializableObj = new stdClass();
            $serializableObj->__class = get_class($var);
            foreach (get_object_vars($var) as $key => $value) {
                try {
                    $serializableObj->$key = $this->makeSerializable($value);
                } catch (Exception $e) {
                    /** @var object $value */
                    $serializableObj->$key = $this->translator->translate('NOT_SERIALIZABLE') . get_class($value);
                }
            }
            return $serializableObj;
        }

        if (is_array($var)) {
            $serializableArray = [];
            foreach ($var as $key => $value) {
                try {
                    $serializableArray[$key] = $this->makeSerializable($value);
                } catch (Exception $e) {
                    $serializableArray[$key] = $this->translator->translate('NOT_SERIALIZABLE') . (
                        is_object($value) ?
                            get_class($value) :
                            gettype($value)
                        );
                }
            }
            return $serializableArray;
        }

        return $var;
    }

    /**
     * Restore unserializable data
     *
     * @param mixed $var The variable to restore
     * @param array $allowedClasses An array of allowed class names
     * @return mixed The restored variable
     * @throws ReflectionException
     */
    private function restoreUnserializable($var, array $allowedClasses)
    {
        if (is_object($var)) {
            if (isset($var->__class)) {
                $className = $var->__class;
                if ($this->isAllowedClass($className, $allowedClasses)) {
                    $reflection = new ReflectionClass($className);
                    $restoredObject = $reflection->newInstanceWithoutConstructor();
                    unset($var->__class);
                    foreach (get_object_vars($var) as $key => $value) {
                        $restoredObject->$key = $this->restoreUnserializable($value, $allowedClasses);
                    }
                    return $restoredObject;
                }
            }

            $result = new stdClass();
            foreach (get_object_vars($var) as $key => $value) {
                if ($key !== '__class') {
                    $result->$key = $this->restoreUnserializable($value, $allowedClasses);
                }
            }
            return $result;
        }

        if (is_array($var)) {
            $result = [];
            foreach ($var as $key => $value) {
                $result[$key] = $this->restoreUnserializable($value, $allowedClasses);
            }
            return $result;
        }

        return $var;
    }

    /**
     * Check if a class is allowed based on inheritance
     *
     * @param string $className The class name to check
     * @param array $allowedClasses An array of allowed class names
     * @return bool Whether the class is allowed
     */
    private function isAllowedClass(string $className, array $allowedClasses): bool
    {
        if (in_array($className, $allowedClasses, true)) {
            return true;
        }

        foreach ($allowedClasses as $allowedClass) {
            if (is_subclass_of($className, $allowedClass)) {
                return true;
            }
        }

        return false;
    }
}
