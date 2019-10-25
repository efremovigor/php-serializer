<?php

namespace Test\Kluatr\Serializer\Entity;

use Kluatr\Serializer\Serializer;
use Kluatr\Serializer\PropertyStrictAccessInterface;

class BigDataEntityStrict extends AbstractBigDataEntity implements PropertyStrictAccessInterface
{

    /**
     * Список свойств с типами
     * Формат ['id'=> 'int','isCreated'=>'bool','product'=> PetProductPlainOrm::class,'productList'=> PetProductPlainOrmList::class,]
     * @return array
     */
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
            'listData'  => ['type' => Serializer::TYPE_OBJECT, 'class' => BigDataListStrict::class],
        ];
    }

    public function __construct()
    {
        $this->listData = new BigDataListStrict();
    }

    /**
     * @return BigDataListStrict
     */
    public function getListData(): BigDataListStrict
    {
        return $this->listData;
    }

    /**
     * @param BigDataListStrict $listData
     */
    public function setListData(BigDataListStrict $listData): void
    {
        $this->listData = $listData;
    }
}