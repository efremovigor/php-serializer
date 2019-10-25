<?php


namespace Test\Kluatr\Serializer\Entity;


use Kluatr\Serializer\HasJsonPropertiesInterface;
use Kluatr\Serializer\PropertyAccessInterface;

class TestClassWithJsonObject implements PropertyAccessInterface , HasJsonPropertiesInterface
{
    protected $a;
    protected $json;

    public function __construct()
    {
        $this->json = new TestClassChild();
    }

    public function getJsonProperties(): array
    {
        return  ['json'];
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return [
            'a',
            'json'
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
     * @return TestClassChild
     */
    public function getJson(): TestClassChild
    {
        return $this->json;
    }

    /**
     * @param TestClassChild $json
     */
    public function setJson(TestClassChild $json): void
    {
        $this->json = $json;
    }


}