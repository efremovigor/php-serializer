<?php

namespace Lib\Serializer\Components;


use Lib\Serializer\ContainsCollectionInterface;
use Lib\Serializer\Error\EntityIsNotChosenException;

class EntityList extends AbstractList implements ContainsCollectionInterface
{

    /**
     * Имя класса списка
     * @return string
     * @throws EntityIsNotChosenException
     */
    public function getClass(): string
    {
        throw new EntityIsNotChosenException('Entity is not chosen');
    }
}