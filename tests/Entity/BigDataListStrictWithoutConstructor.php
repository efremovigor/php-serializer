<?php

namespace Test\Lib\Serializer\Entity;

use Lib\Serializer\ContainsCollectionInterface;

class BigDataListStrictWithoutConstructor extends AbstractList implements ContainsCollectionInterface
{

    /**
     * Имя класса списка
     * @return string
     */
    public function getClass(): string
    {
        return BigDataEntityStrictWithoutConstructor::class;
    }
}