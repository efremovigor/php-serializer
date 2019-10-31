<?php


namespace Test\Lib\Serializer\Entity;


use Lib\Serializer\Serializer;
use Lib\Serializer\PropertyStrictAccessInterface;

class TestClassStrictWithJsonObject implements PropertyStrictAccessInterface
{
    protected $a;
    protected $json;

    public function __construct()
    {
        $this->json = new TestClassChild();
    }

    /**
     * @return array
     */
    public function getPropertiesStrict(): array
    {
        return [
            'a'    => ['type' => Serializer::TYPE_STRING],
            'json' => ['type' => Serializer::TYPE_OBJECT | Serializer::TYPE_JSON],
        ];
    }

    /**
     * @return mixed
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * @param mixed $a
     */
    public function setA($a): void
    {
        $this->a = $a;
    }

    /**
     * @return
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @param $json
     */
    public function setJson($json): void
    {
        $this->json = $json;
    }
}