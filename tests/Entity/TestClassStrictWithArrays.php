<?php


namespace Test\Kluatr\Serializer\Entity;


use Kluatr\Serializer\PropertyStrictAccessInterface;
use Kluatr\Serializer\Serializer;

class TestClassStrictWithArrays implements PropertyStrictAccessInterface
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string|null
     */
    protected $qwe;

    /**
     * Список свойств с типами
     * Формат ['propertyName'=>['type'=>'mask of type','class'=>'classname']]
     * @return array
     */
    public function getPropertiesStrict(): array
    {
        return [
            'data' => ['type' => Serializer::TYPE_ARRAY],
            'qwe'  => ['type' => Serializer::TYPE_STRING | Serializer::TYPE_NULL],
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

    /**
     * @param $key
     * @param $item
     */
    public function addData($key, $item): void
    {
        $this->data[$key] = $item;
    }

    /**
     * @return string|null
     */
    public function getQwe(): ?string
    {
        return $this->qwe;
    }

    /**
     * @param string|null $qwe
     */
    public function setQwe(?string $qwe): void
    {
        $this->qwe = $qwe;
    }
}