<?php

namespace Test\Kluatr\Serializer\Entity;


use Kluatr\Serializer\HasJsonPropertiesInterface;
use Kluatr\Serializer\PropertyAccessInterface;

class TestClass implements PropertyAccessInterface, HasJsonPropertiesInterface
{
    private $a;
    private $b;
    private $c;
    private $isX;
    private $json;

    public function __construct()
    {
        $class   = new class implements PropertyAccessInterface
        {
            private $f;

            public function getF()
            {
                return $this->f;
            }

            public function setF($f): void
            {
                $this->f = $f;
            }

            public function getProperties(): array
            {
                return ['f'];
            }
        };
        $this->c = new $class();
    }

    public function getA()
    {
        return $this->a;
    }

    public function setA($a): void
    {
        $this->a = $a;
    }

    public function getB()
    {
        return $this->b;
    }

    public function addB($b): void
    {
        $this->b[] = $b;
    }

    public function getC()
    {
        return $this->c;
    }

    public function setC($c): void
    {
        $this->c = $c;
    }

    public function getProperties(): array
    {
        return ['a', 'b', 'c', 'isX', 'json'];
    }

    public function setB($b): void
    {
        $this->b = $b;
    }

    /**
     * @return mixed
     */
    public function isX()
    {
        return $this->isX;
    }

    /**
     * @param mixed $isX
     */
    public function setIsX($isX): void
    {
        $this->isX = $isX;
    }

    /**
     * @return mixed
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @param mixed $json
     */
    public function setJson($json): void
    {
        $this->json = $json;
    }

    public function getJsonProperties(): array
    {
        return ['json'];
    }
}
