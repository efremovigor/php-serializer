<?php

namespace Test\Lib\Serializer\Entity;

use Lib\Serializer\ContainsCollectionInterface;

class TestList3 extends AbstractList implements ContainsCollectionInterface
{


    /**
     * Имя класса списка
     * @return string
     */
    public function getClass(): string
    {
        return TestClass::class;
    }
}