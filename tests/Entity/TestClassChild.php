<?php


namespace Test\Kluatr\Serializer\Entity;


class TestClassChild extends TestClass
{
    protected $x;
    protected $z;

    public function getProperties(): array
    {
        return array_merge(
            parent::getProperties(),
            [
                'x',
                'z',
            ]
        );
    }

    /**
     * @return mixed
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param mixed $x
     */
    public function setX($x): void
    {
        $this->x = $x;
    }

    /**
     * @return mixed
     */
    public function getZ()
    {
        return $this->z;
    }

    /**
     * @param mixed $z
     */
    public function setZ($z): void
    {
        $this->z = $z;
    }

}
