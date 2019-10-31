<?php


namespace Test\Lib\Serializer\Entity;


use Lib\Serializer\PropertyStrictAccessInterface;
use Lib\Serializer\Serializer;

class TestMigrationClass3 implements PropertyStrictAccessInterface
{
    /**
     * @var string
     */
    protected $string1;

    /**
     * @var int
     */
    protected $int1;

    /**
     * @var TestMigrationClass3|null
     */
    protected $object1;

    /**
     * @var int
     */
    protected $int;

    /**
     * @var bool
     */
    protected $isBool = false;

    /**
     * @var array
     */
    protected $array = [];

    /**
     * @var float|null
     */
    protected $float;

    /**
     * @var float
     */
    protected $float0;

    /**
     * Список свойств с типами
     * Формат ['propertyName'=>['type'=>'mask of type','class'=>'classname']]
     * @return array
     */
    public function getPropertiesStrict(): array
    {
        return [
            'string1' => ['type' => Serializer::TYPE_STRING],
            'int1'    => ['type' => Serializer::TYPE_INT],
            'object1' => ['type' => Serializer::TYPE_OBJECT, 'class' => TestMigrationClass3::class],
            'int'     => ['type' => Serializer::TYPE_INT],
            'isBool'  => ['type' => Serializer::TYPE_BOOL],
            'array'   => ['type' => Serializer::TYPE_ARRAY],
            'float'   => ['type' => Serializer::TYPE_FLOAT | Serializer::TYPE_NULL],
            'float0'  => ['type' => Serializer::TYPE_FLOAT],
        ];
    }

    /**
     * @return string
     */
    public function getString1(): string
    {
        return $this->string1;
    }

    /**
     * @param string $string1
     */
    public function setString1(string $string1): void
    {
        $this->string1 = $string1;
    }

    /**
     * @return int
     */
    public function getInt1(): int
    {
        return $this->int1;
    }

    /**
     * @param int $int1
     */
    public function setInt1(int $int1): void
    {
        $this->int1 = $int1;
    }

    /**
     * @return TestMigrationClass3|null
     */
    public function getObject1(): ?TestMigrationClass3
    {
        return $this->object1;
    }

    /**
     * @param TestMigrationClass3|null $object1
     */
    public function setObject1(?TestMigrationClass3 $object1): void
    {
        $this->object1 = $object1;
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
}