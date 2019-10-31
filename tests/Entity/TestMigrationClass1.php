<?php

namespace Test\Lib\Serializer\Entity;


use Lib\Serializer\MigrationEntityInterface;
use Lib\Serializer\Serializer;
use Lib\Serializer\PropertyStrictAccessInterface;

class TestMigrationClass1 implements MigrationEntityInterface, PropertyStrictAccessInterface
{
    /**
     * @var string
     */
    protected $string;

    /**
     * @var int
     */
    protected $int;

    /**
     * @var bool
     */
    protected $isBool;

    /**
     * @var array
     */
    protected $array = [];

    /**
     * @var float|null
     */
    protected $float;

    /**
     * @var TestMigrationClass1
     */
    protected $object;

    /**
     * @var float
     */
    protected $float0;

    /**
     * [
     *  ClassName::class => [
     *      nativePropertyName => externalPropertyName
     *  ...
     * ]
     *  ClassName1::class => []
     * ...
     * ]
     * @return array
     */
    public function getEntityRelations(): array
    {
        return [
            TestMigrationClass2::class => [
                'string' => 'string1',
                'int'    => 'int1',
                'object' => 'object1',
                'isBool' => 'isBool2',
            ],
            TestMigrationClass3::class => [
                'string' => 'string1',
                'int'    => 'int1',
                'object' => 'object1',
                'isBool' => 'isBool2',
            ],
        ];
    }

    /**
     * Список свойств с типами
     * Формат ['propertyName'=>['type'=>'mask of type','class'=>'classname']]
     * @return array
     */
    public function getPropertiesStrict(): array
    {
        return [
            'string' => ['type' => Serializer::TYPE_STRING],
            'int'    => ['type' => Serializer::TYPE_INT],
            'isBool' => ['type' => Serializer::TYPE_BOOL],
            'array'  => ['type' => Serializer::TYPE_ARRAY],
            'float'  => ['type' => Serializer::TYPE_FLOAT | Serializer::TYPE_NULL],
            'object' => ['type' => Serializer::TYPE_OBJECT | Serializer::TYPE_NULL, 'class' => TestMigrationClass1::class],
            'float0' => ['type' => Serializer::TYPE_FLOAT],
        ];
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @param string $string
     */
    public function setString(string $string): void
    {
        $this->string = $string;
    }

    /**
     * @return int
     */
    public function getInt(): int
    {
        return $this->int;
    }

    /**
     * @param int $int
     */
    public function setInt(int $int): void
    {
        $this->int = $int;
    }

    /**
     * @return bool
     */
    public function isBool(): bool
    {
        return $this->isBool;
    }

    /**
     * @param bool $isBool
     */
    public function setIsBool(bool $isBool): void
    {
        $this->isBool = $isBool;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }

    /**
     * @param $key
     * @param $item
     */
    public function addArray($key, $item): void
    {
        $this->array[$key] = $item;
    }

    /**
     * @param array $array
     */
    public function setArray(array $array): void
    {
        $this->array = $array;
    }

    /**
     * @return float|null
     */
    public function getFloat(): ?float
    {
        return $this->float;
    }

    /**
     * @param float|null $float
     */
    public function setFloat(?float $float): void
    {
        $this->float = $float;
    }

    /**
     * @return TestMigrationClass1|null
     */
    public function getObject(): ?TestMigrationClass1
    {
        return $this->object;
    }

    /**
     * @param TestMigrationClass1|null $object
     */
    public function setObject(?TestMigrationClass1 $object): void
    {
        $this->object = $object;
    }

    /**
     * @return float
     */
    public function getFloat0(): float
    {
        return $this->float0;
    }

    /**
     * @param float $float0
     */
    public function setFloat0(float $float0): void
    {
        $this->float0 = $float0;
    }
}