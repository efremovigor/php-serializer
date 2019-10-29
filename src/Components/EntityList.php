<?php

namespace Kluatr\Serializer\Components;


use Kluatr\Serializer\ContainsCollectionInterface;
use Kluatr\Serializer\Error\EntityIsNotChosenException;

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