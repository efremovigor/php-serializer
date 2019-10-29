<?php

namespace Kluatr\Serializer\Components;

/**
 * Для сортировки внутри листа
 * Class SortBy
 * @package Kluatr\Serializer\Components
 */
class SortBy extends OrderBy
{

    private $type;


    public function __construct(string $field = null, string $type = 'string')
    {
        parent::__construct($field);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function sortInteger(array $elements, string $method): array
    {
        usort(
            $elements,
            function($a, $b) use ($method)
            {
                if ($a->$method() == $b->$method()) {
                    return 0;
                }

                return ($a->$method() < $b->$method()) ? -1 : 1;
            }
        );

        return $this->getDirection() === 'DESC' ? array_reverse($elements) : $elements;
    }

    public function sortString(array $elements, string $method): array
    {
        usort(
            $elements,
            function($a, $b) use ($method)
            {
                return strcmp($a->$method(), $b->$method());
            }
        );

        return $this->getDirection() === 'DESC' ? array_reverse($elements) : $elements;
    }
}