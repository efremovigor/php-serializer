<?php

namespace Test\Lib\Serializer\Entity;

use Lib\Serializer\ContainsCollectionInterface;

class TestCollectionClass2 extends AbstractList implements ContainsCollectionInterface
{

    /**
     * Имя класса списка
     * @return string
     */
    public function getClass(): string
    {
        return TestClass::class;
    }

    /**
     * Получение элементов списка
     * @return TestClass[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }
}
