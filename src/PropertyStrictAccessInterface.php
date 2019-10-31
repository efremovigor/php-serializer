<?php

namespace Lib\Serializer;

/**
 * Interface PropertyStrictAccessInterface
 * @package Kluatr\Serializer
 */
interface PropertyStrictAccessInterface
{
    /**
     * Список свойств с типами
     * Формат ['propertyName'=>['type'=>'mask of type','class'=>'classname']]
     * @return array
     */
    public function getPropertiesStrict(): array;
}