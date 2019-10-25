<?php

namespace Test\Kluatr\Serializer\Entity;

use Kluatr\Serializer\ContainsCollectionInterface;

class BigDataList extends AbstractList implements ContainsCollectionInterface
{

    /**
     * Имя класса списка
     * @return string
     */
    public function getClass(): string
    {
        return BigDataEntity::class;
    }
}