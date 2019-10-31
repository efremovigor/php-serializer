<?php


namespace Test\Lib\Serializer\Entity;


use Lib\Serializer\Serializer;
use Lib\Serializer\PropertyStrictAccessInterface;

class BigDataEntityStrictWithoutConstructor extends AbstractBigDataEntity implements PropertyStrictAccessInterface
{
    public function getPropertiesStrict(): array
    {
        return [
            'asdfg1'    => ['type' => Serializer::TYPE_INT],
            'asdfg2'    => ['type' => Serializer::TYPE_INT],
            'asdfg3'    => ['type' => Serializer::TYPE_INT],
            'asdfg4'    => ['type' => Serializer::TYPE_INT],
            'asdfg5'    => ['type' => Serializer::TYPE_INT],
            'asdfg6'    => ['type' => Serializer::TYPE_FLOAT],
            'asdfg7'    => ['type' => Serializer::TYPE_FLOAT],
            'asdfg8'    => ['type' => Serializer::TYPE_FLOAT],
            'asdfg9'    => ['type' => Serializer::TYPE_FLOAT],
            'asdfg0'    => ['type' => Serializer::TYPE_FLOAT],
            'asdfg11'   => ['type' => Serializer::TYPE_STRING],
            'asdfg12'   => ['type' => Serializer::TYPE_STRING],
            'asdfg13'   => ['type' => Serializer::TYPE_STRING],
            'asdfg14'   => ['type' => Serializer::TYPE_STRING],
            'asdfg15'   => ['type' => Serializer::TYPE_STRING],
            'asdfg16'   => ['type' => Serializer::TYPE_STRING],
            'asdfg17'   => ['type' => Serializer::TYPE_STRING],
            'asdfg18'   => ['type' => Serializer::TYPE_STRING],
            'isAsdfg19' => ['type' => Serializer::TYPE_BOOL],
            'isAsdfg20' => ['type' => Serializer::TYPE_BOOL],
            'isAsdfg21' => ['type' => Serializer::TYPE_BOOL],
            'listData'  => ['type' => Serializer::TYPE_OBJECT | Serializer::TYPE_NULL , 'class' => BigDataListStrict::class],
        ];
    }

    /**
     * @return BigDataListStrict
     */
    public function getListData(): ?BigDataListStrict
    {
        return $this->listData;
    }

    /**
     * @param BigDataListStrict $listData
     */
    public function setListData(?BigDataListStrict $listData): void
    {
        $this->listData = $listData;
    }
}