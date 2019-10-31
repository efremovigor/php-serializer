<?php

namespace Lib\Serializer;

/**
 * @deprecated
 * Предоставляет доступный список имен свойств
 * Interface PropertyAccessInterface
 * @package Helpers
 */
interface PropertyAccessInterface
{
    /**
     * @return array
     */
    public function getProperties(): array;
}