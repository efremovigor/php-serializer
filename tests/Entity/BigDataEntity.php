<?php

namespace Test\Kluatr\Serializer\Entity;


use Kluatr\Serializer\PropertyAccessInterface;

class BigDataEntity extends AbstractBigDataEntity implements PropertyAccessInterface
{

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return [
            'asdfg1',
            'asdfg2',
            'asdfg3',
            'asdfg4',
            'asdfg5',
            'asdfg6',
            'asdfg7',
            'asdfg8',
            'asdfg9',
            'asdfg0',
            'asdfg11',
            'asdfg12',
            'asdfg13',
            'asdfg14',
            'asdfg15',
            'asdfg16',
            'asdfg17',
            'asdfg18',
            'isAsdfg19',
            'isAsdfg20',
            'isAsdfg21',
            'listData',
        ];
    }

    public function __construct()
    {
        $this->listData = new BigDataList();
    }

    /**
     * @return BigDataList
     */
    public function getListData() :BigDataList
    {
        return $this->listData;
    }

    /**
     * @param BigDataList $listData
     */
    public function setListData(BigDataList $listData): void
    {
        $this->listData = $listData;
    }

}