<?php

namespace Test\Lib\Serializer\Entity;

use Lib\Serializer\ContainsCollectionInterface;

class TestList extends AbstractList implements ContainsCollectionInterface
{


    /**
     * Имя класса списка
     * @return string
     */
    public function getClass(): string
    {
        return TestList2::class;
    }
}
