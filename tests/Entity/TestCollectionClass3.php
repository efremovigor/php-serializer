<?php

namespace Test\Lib\Serializer\Entity;

use Lib\Serializer\ContainsCollectionInterface;

class TestCollectionClass3 extends AbstractList implements ContainsCollectionInterface
{

    /**
     * Имя класса списка
     * @return string
     */
    public function getClass(): string
    {
        return TestClassChild::class;
    }

    /**
     * Получение элементов списка
     * @return TestClassChild[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }
}
