<?php

namespace Test\Kluatr\Serializer\Entity;

use Kluatr\Serializer\ContainsCollectionInterface;

class TestCollectionClass extends AbstractList implements ContainsCollectionInterface
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
     * Сохранение элемента списка
     * @param $key
     * @param TestClass $element
     */
    public function set($key, $element): void
    {
        parent::set($element->getA(), $element);
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
