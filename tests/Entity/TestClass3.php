<?php


namespace Test\Lib\Serializer\Entity;


use Lib\Serializer\PropertyAccessInterface;

class TestClass3 implements PropertyAccessInterface
{

    private $prop1;
    private $prop2Prop;
    private $prop3Prop;
    private $prop4;

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return [
            '_prop1',
            '_prop2_prop',
            '_prop3_prop_',
            'prop4',
        ];
    }

    /**
     * @return mixed
     */
    public function getProp1()
    {
        return $this->prop1;
    }

    /**
     * @param mixed $prop1
     */
    public function setProp1($prop1): void
    {
        $this->prop1 = $prop1;
    }

    /**
     * @return mixed
     */
    public function getProp2Prop()
    {
        return $this->prop2Prop;
    }

    /**
     * @param mixed $prop2Prop
     */
    public function setProp2Prop($prop2Prop): void
    {
        $this->prop2Prop = $prop2Prop;
    }

    /**
     * @return mixed
     */
    public function getProp3Prop()
    {
        return $this->prop3Prop;
    }

    /**
     * @param mixed $prop3Prop
     */
    public function setProp3Prop($prop3Prop): void
    {
        $this->prop3Prop = $prop3Prop;
    }

    /**
     * @return mixed
     */
    public function getProp4()
    {
        return $this->prop4;
    }

    /**
     * @param mixed $prop4
     */
    public function setProp4($prop4): void
    {
        $this->prop4 = $prop4;
    }


}
