<?php

namespace Lib\Serializer\Components;

/**
 * Для сортировки внутри листа
 * @package Kluatr\Serializer\Components
 */
class OrderBy
{

    private $field;
    private $direction = 'ASC';


    public function __construct(string $field = null)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }


    public function setDirectionASC()
    {
        $this->direction = 'ASC';
    }


    public function setDirectionDESC()
    {
        $this->direction = 'DESC';
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }
}