<?php


namespace Test\Kluatr\Serializer\Entity;


use Kluatr\Serializer\PropertyAccessInterface;
use Kluatr\Serializer\Serializer;

class TestClassWithArrays implements PropertyAccessInterface
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return [
            'data',
        ];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function addData($item){
        $this->data[] = $item;
    }
}