<?php

namespace Test\Kluatr\Serializer\Entity;

use Kluatr\Serializer\ContainsCollectionInterface;

class TestList2 extends AbstractList implements ContainsCollectionInterface
{

    /**
     * Имя класса списка
     * @return string
     */
    public function getClass(): string
    {
        return TestList3::class;
    }
}
