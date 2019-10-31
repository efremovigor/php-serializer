<?php

namespace Test\Lib\Serializer\Entity;

use Lib\Serializer\ContainsCollectionInterface;

class BigDataListStrict extends AbstractList implements ContainsCollectionInterface
{

    /**
     * Имя класса списка
     * @return string
     */
    public function getClass(): string
    {
        return BigDataEntityStrict::class;
    }
}