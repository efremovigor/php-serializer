<?php

namespace Test\Kluatr\Serializer;

use Exception;
use Kluatr\Serializer\Error\EntityIsNotChosenException;
use Kluatr\Serializer\Error\EntityIsNotDescribedException;
use Kluatr\Serializer\Error\PropertyWithUnknownTypeException;
use PHPUnit\Framework\TestCase;
use Kluatr\Serializer\Serializer;
use stdClass;
use Test\Kluatr\Serializer\Entity\BigDataEntity;
use Test\Kluatr\Serializer\Entity\BigDataEntityStrict;
use Test\Kluatr\Serializer\Entity\BigDataEntityStrictWithoutConstructor;
use Test\Kluatr\Serializer\Entity\BigDataList;
use Test\Kluatr\Serializer\Entity\BigDataListStrict;
use Test\Kluatr\Serializer\Entity\BigDataListStrictWithoutConstructor;
use Test\Kluatr\Serializer\Entity\SerializerFixtures;
use Test\Kluatr\Serializer\Entity\SerializerOld;
use Test\Kluatr\Serializer\Entity\TestClass;
use Test\Kluatr\Serializer\Entity\TestClass3;
use Test\Kluatr\Serializer\Entity\TestClass3Strict;
use Test\Kluatr\Serializer\Entity\TestClassChild;
use Test\Kluatr\Serializer\Entity\TestClassStrictWithArrays;
use Test\Kluatr\Serializer\Entity\TestClassStrictWithJsonObject;
use Test\Kluatr\Serializer\Entity\TestClassWithArrays;
use Test\Kluatr\Serializer\Entity\TestClassWithJsonObject;
use Test\Kluatr\Serializer\Entity\TestCollectionClass;
use Test\Kluatr\Serializer\Entity\TestCollectionClass2;
use Test\Kluatr\Serializer\Entity\TestCollectionClass3;
use Test\Kluatr\Serializer\Entity\TestList;
use Test\Kluatr\Serializer\Entity\TestMigrationClass1;
use Test\Kluatr\Serializer\Entity\TestMigrationClass2;

class SerializerTest extends TestCase
{

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var SerializerOld
     */
    private $serializerOld;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->serializer    = new Serializer();
        $this->serializerOld = new SerializerOld();
    }

    /**
     * Тест обычного заполнение сущности массивом
     * @throws Exception
     */
    public function testNormalizeArray(): void
    {
        $fixture = ['a' => 'a1', 'b' => ['b1', 'b2', 'b3'], 'c' => ['f' => 'f1'], 'isX' => true];
        $object  = $this->getAnonymousObject();
        $this->assertEquals($object->getA(), null);
        $this->assertEquals($object->getB(), null);
        $this->assertEquals($object->getC()->getF(), null);
        $this->assertEquals($object->isX(), null);
        $object = $this->serializer->normalize($fixture, $object);
        $this->assertEquals($object->getA(), $fixture['a']);
        $this->assertEquals($object->getB(), $fixture['b']);
        $this->assertEquals($object->getC()->getF(), $fixture['c']['f']);
        $this->assertEquals($object->isX(), $fixture['isX']);
    }

    /**
     * Тест обычного заполнение сущности другой сущностью
     * @throws Exception
     */
    public function testNormalizeObject(): void
    {
        $object1 = $this->getAnonymousObject();
        $object1->setA('a1');
        $object1->addB('b1');
        $object1->addB('b2');
        $object1->addB('b3');
        $object1->getC()->setF('f1');
        $object1->setIsX(true);
        $object2 = $this->getAnonymousObject();
        $object2 = $this->serializer->normalize($object1, $object2);
        $this->assertEquals($object2->getA(), 'a1');
        $this->assertEquals($object2->getB(), ['b1', 'b2', 'b3']);
        $this->assertEquals($object2->getC()->getF(), 'f1');
        $this->assertEquals($object2->isX(), true);
    }

    /**
     * Тест заполнение сущности другой сущностью с обнулением параметров
     * @throws Exception
     */
    public function testNormalizeObjectNullable(): void
    {
        $object1 = $this->getAnonymousObject();
        $object2 = $this->getAnonymousObject();
        $object2->setA('a1');
        $object2->getC()->setF('f1');
        $object2->addB('b1');
        $object2->addB('b2');
        $object2->addB('b3');
        $object2->setIsX(true);
        $object2 = $this->serializer->normalize($object1, $object2);
        $this->assertEquals($object2->getA(), 'a1');
        $this->assertEquals($object2->getB(), ['b1', 'b2', 'b3']);
        $this->assertEquals($object2->getC()->getF(), 'f1');
        $this->assertEquals($object2->isX(), true);

        $object2 = $this->getAnonymousObject();
        $object2->setA('a1');
        $object2->addB('b1');
        $object2->addB('b2');
        $object2->addB('b3');
        $object2->getC()->setF('f1');
        $object2 = $this->serializer->normalize($object1, $object2, Serializer::NULLABLE | Serializer::REWRITABLE);
        $this->assertEquals($object2->getA(), null);
        $this->assertEquals($object2->getB(), null);
        $this->assertEquals($object2->getC()->getF(), null);
        $this->assertEquals($object2->isX(), null);
    }

    /**
     * Тест заполнение сущности массивом с обнулением параметров
     */
    public function testNormalizeArrayNullable(): void
    {
        $fixture = ['a' => null, 'b' => ['b1', 'b2', 'b3'], 'c' => ['f' => null], 'isX' => null];
        $object  = $this->getAnonymousObject();
        $object->setA('a1');
        $object->getC()->setF('f1');
        $object->setIsX(true);
        $object = $this->serializer->normalize($fixture, $object);
        $this->assertEquals($object->getA(), 'a1');
        $this->assertEquals($object->getB(), $fixture['b']);
        $this->assertEquals($object->getC()->getF(), 'f1');
        $this->assertEquals($object->isX(), true);

        $object = $this->getAnonymousObject();
        $object->setA('a1');
        $object->addB('b1');
        $object->addB('b2');
        $object->addB('b3');
        $object->getC()->setF('f1');
        $object = $this->serializer->normalize(['a' => null, 'b' => null, 'c' => ['f' => null]], $object, Serializer::NULLABLE | Serializer::REWRITABLE);
        $this->assertEquals($object->getA(), null);
        $this->assertEquals($object->getB(), null);
        $this->assertEquals($object->getC()->getF(), null);
        $this->assertEquals($object->isX(), null);

    }

    /**
     * Тест параметра добавления данных в коллекции (обьектом)
     */
    public function testNormalizeObjectAddable(): void
    {
        $object1 = $this->getAnonymousObject();
        $object1->addB('b2');
        $object2 = $this->getAnonymousObject();
        $object2->addB('b1');

        $object2 = $this->serializer->normalize($object1, $object2, 0);
        $this->assertEquals($object2->getB(), ['b1']);

        $object2 = $this->serializer->normalize($object1, $object2);
        $this->assertEquals($object2->getB(), ['b1', 'b2']);
    }

    /**
     * Тест параметра добавления данных в коллекции (массивом)
     */
    public function testNormalizeArrayAddable(): void
    {
        $fixture = ['b' => ['b1', 'b2', 'b3']];
        $object2 = $this->getAnonymousObject();
        $object2->addB('b1');

        $object2 = $this->serializer->normalize($fixture, $object2, 0);
        $this->assertEquals($object2->getB(), ['b1']);

        $object2 = $this->serializer->normalize($fixture, $object2);
        $this->assertEquals($object2->getB(), ['b1', 'b1', 'b2', 'b3']);
    }

    public function testNormalizeJsonToObject(): void
    {
        $json   = '{"a":"a1","b":["b1","b2","b3"],"c":{"f":"f1"},"isX":true}';
        $object = $this->getAnonymousObject();
        $object = $this->serializer->normalize($json, $object);
        $this->assertEquals($object->getA(), 'a1');
        $this->assertEquals($object->getB(), ['b1', 'b2', 'b3']);
        $this->assertEquals($object->getC()->getF(), 'f1');
        $this->assertEquals($object->isX(), true);
    }

    /**
     * @throws Exception
     */
    public function testNormalizeObjectToArray(): void
    {
        $object = $this->getAnonymousObject();
        $object->setA('a1');
        $object->addB('b1');
        $object->addB('b2');
        $object->addB('b3');
        $object->setIsX(true);
        $object->getC()->setF('f1');
        $object2 = $this->serializer->normalize($object);
        $this->assertEquals($object2['a'], 'a1');
        $this->assertEquals($object2['b'], ['b1', 'b2', 'b3']);
        $this->assertEquals($object2['c']['f'], 'f1');
        $this->assertEquals($object2['isX'], true);
    }

    public function testNormalizeObjectToArrayFullRewrite(): void
    {
        $object = $this->getAnonymousObject();
        $object->setA('a1');
        $object->addB('b1');
        $object->addB('b2');
        $object->addB('b3');
        $object->getC()->setF('f1');
        $object->setIsX(true);
        $object2 = $this->serializer->normalize($object, ['a' => 'a2', 'b' => ['b4'], 'c' => ['f' => 'f8'], 'isX' => false], Serializer::ADDABLE | Serializer::REWRITABLE);
        $this->assertEquals($object2['a'], 'a1');
        $this->assertEquals($object2['b'], ['b1', 'b2', 'b3', 'b4']);
        $this->assertEquals($object2['c']['f'], 'f1');
        $this->assertEquals($object2['isX'], true);
    }

    public function testNormalizeObjectToArrayFullNull(): void
    {
        $object  = $this->getAnonymousObject();
        $object2 = $this->serializer->normalize($object, ['a' => 'a1', 'b' => ['b1', 'b2', 'b3', 'b4'], 'c' => ['f' => 'f1'], 'isX' => true], Serializer::REWRITABLE);
        $this->assertEquals($object2['a'], 'a1');
        $this->assertEquals($object2['b'], ['b1', 'b2', 'b3', 'b4']);
        $this->assertEquals($object2['c']['f'], 'f1');
        $this->assertEquals($object2['isX'], true);
        $object2 = $this->serializer->normalize($object, ['a' => 'a1', 'b' => ['b1', 'b2', 'b3', 'b4'], 'c' => ['f' => 'f1']], Serializer::NULLABLE | Serializer::REWRITABLE);
        $this->assertEquals($object2['a'], null);
        $this->assertEquals($object2['b'], null);
        $this->assertEquals($object2['c']['f'], null);
        $this->assertEquals($object2['isX'], null);
    }

    public function testSerializeObjectToJson(): void
    {
        $object = $this->getAnonymousObject();
        $object->setA('a1');
        $object->addB('b1');
        $object->addB('b2');
        $object->addB('b3');
        $object->getC()->setF('f1');
        $object->setIsX(true);
        $json = $this->serializer->serialize($object);
        $this->assertEquals('{"a":"a1","b":["b1","b2","b3"],"c":{"f":"f1"},"isX":true,"json":null}', $json);
    }

    public function testSerializeArrayToJson(): void
    {
        $json = $this->serializer->serialize(
            ['a' => 'a1', 'b' => ['b1', 'b2', 'b3', 'b4'], 'c' => ['f' => 'f1'], 'isX' => false]
        );
        $this->assertEquals('{"a":"a1","b":["b1","b2","b3","b4"],"c":{"f":"f1"},"isX":false}', $json);
    }

    /**
     * @throws Exception
     */
    public function testNormalizeJsonToClass(): void
    {
        $object = $this->serializer->normalize(
            '{"a":"a1","b":["b1","b2","b3","b4"],"c":{"f":"f1"},"isX":true}',
            TestClass::class
        );
        $this->assertEquals($object->getA(), 'a1');
        $this->assertEquals($object->getB(), ['b1', 'b2', 'b3', 'b4']);
        $this->assertEquals($object->getC()->getF(), 'f1');
        $this->assertEquals($object->isX(), true);
    }

    public function testSerializeComplexObjectToJson(): void
    {
        $object1 = $this->getAnonymousObject();
        $object2 = $this->getAnonymousObject();
        $object3 = $this->getAnonymousObject();

        $object1->setA('a1');
        $object1->addB($object2);
        $object1->getC()->setF('f1');

        $object2->setA('a2');
        $object2->addB($object3);
        $object2->addB('b21');
        $object2->addB('b21');
        $object2->addB('b21');
        $object2->addB('b21');
        $object2->getC()->setF('f2');
        $object2->setIsX(false);


        $object3->setA('a3');
        $object3->addB('b31');
        $object3->addB('b31');
        $object3->addB('b31');
        $object3->addB('b31');
        $object3->getC()->setF('f3');
        $object3->setIsX(true);

        $json = $this->serializer->serialize($object1);
        $this->assertEquals(
            '{"a":"a1","b":[{"a":"a2","b":[{"a":"a3","b":["b31","b31","b31","b31"],"c":{"f":"f3"},"isX":true,"json":null},"b21","b21","b21","b21"],"c":{"f":"f2"},"isX":false,"json":null}],"c":{"f":"f1"},"isX":null,"json":null}',
            $json
        );
        $jsonFilled = $this->serializer->jsonSignificant($object1);
        $this->assertEquals(
            '{"a":"a1","b":[{"a":"a2","b":[{"a":"a3","b":["b31","b31","b31","b31"],"c":{"f":"f3"},"isX":true},"b21","b21","b21","b21"],"c":{"f":"f2"},"isX":false}],"c":{"f":"f1"}}',
            $jsonFilled
        );
    }

    /**
     * @throws Exception
     */
    public function testSerializeWithFlagSerializeFilled()
    {
        $fixture = ['a' => 'a1', 'b' => ['b1' => null, 'b2', 'b3' => null, 'b4'], 'c' => ['f' => null], 'isX' => false, 'y' => true, 'n' => null];
        $json    = $this->serializer->serialize($fixture, 'json', Serializer::ONLY_FILLED);
        $this->assertEquals($json, '{"a":"a1","b":["b2","b4"],"isX":false,"y":true}');
    }

    /**
     * @throws Exception
     */
    public function testSerializeWithFlagSerializeFilledEmptyArray()
    {
        $fixture = [];
        $json    = $this->serializer->serialize($fixture, 'json', Serializer::ONLY_FILLED);
        $this->assertEquals($json, null);
    }

    /**
     * @throws Exception
     */
    public function testSerializeWithFlagSerializeFilledJson()
    {
        $fixture = '{"a":"a1","b":["b2","b4"],"isX":false,"y":true}';
        $json    = $this->serializer->serialize($fixture, 'json', Serializer::ONLY_FILLED);
        $this->assertEquals($json, $fixture);
    }

    /**
     * @return array
     */
    public function defaultBehaviorProvider()
    {
        return [
            [$this->serializer->serialize(null, true), null],
            [$this->serializer->normalize(null, true), null],
            [$this->serializer->normalize(null, []), null],
            [$this->serializer->normalize(null, 0), null],
            [$this->serializer->normalize(null, 111), null],
            [$this->serializer->normalize(null, '0'), null],
            [$this->serializer->normalize(null, 'fuck'), null],
            [$this->serializer->normalize(true, true), true],
            [$this->serializer->normalize(true, null), true],
            [$this->serializer->normalize(true, false), true],
            [$this->serializer->normalize(true, ''), true],
            [$this->serializer->normalize(true, []), true],
            [$this->serializer->normalize(true, []), true],
            [$this->serializer->normalize(true, []), true],
            [$this->serializer->normalize(true, []), true],
            [$this->serializer->normalize(false, null), false],
            [$this->serializer->normalize(false, false), false],
            [$this->serializer->normalize(false, ''), false],
            [$this->serializer->normalize(false, []), false],
            [$this->serializer->normalize(false, []), false],
            [$this->serializer->normalize(false, []), false],
            [$this->serializer->normalize(false, []), false],
            [$this->serializer->normalize('', ''), ''],
            [$this->serializer->normalize('', null), ''],
            [$this->serializer->normalize('', true), ''],
            [$this->serializer->normalize('', false), ''],
            [$this->serializer->normalize('', []), ''],
            [$this->serializer->normalize([], true), []],
            [$this->serializer->normalize([], false), []],
            [$this->serializer->normalize([], null), []],
            [$this->serializer->normalize([], []), []],
            [$this->serializer->normalize(['keyFuck' => 'fuck1'], []), ['keyFuck' => 'fuck1']],
            [$this->serializer->normalize(['keyFuck' => 'fuck1'], true), ['keyFuck' => 'fuck1']],
            [$this->serializer->normalize(['keyFuck' => 'fuck1'], false), ['keyFuck' => 'fuck1']],
            [$this->serializer->normalize(['keyFuck' => 'fuck1'], null), ['keyFuck' => 'fuck1']],
            [$this->serializer->normalize(['keyFuck' => 'fuck1'], ''), ['keyFuck' => 'fuck1']],
            [$this->serializer->normalize(['keyFuck' => 'fuck1'], '0'), ['keyFuck' => 'fuck1']],
            [$this->serializer->normalize(['keyFuck' => 'fuck1'], 'qwe'), ['keyFuck' => 'fuck1']],
            [$this->serializer->normalize(['keyFuck' => 'fuck1'], 111), ['keyFuck' => 'fuck1']],
            [$this->serializer->normalize('0', []), '0'],
            [$this->serializer->normalize('0', true), '0'],
            [$this->serializer->normalize('0', false), '0'],
            [$this->serializer->normalize('0', null), '0'],
            [$this->serializer->normalize('0', '0'), '0'],
            [$this->serializer->normalize(0, '0'), 0],
            [$this->serializer->normalize(0, true), 0],
            [$this->serializer->normalize(0, false), 0],
            [$this->serializer->normalize(0, null), 0],
            [$this->serializer->normalize(0, []), 0],
            [$this->serializer->normalize(1212, '1212'), 1212],
            [$this->serializer->normalize(1212, true), 1212],
            [$this->serializer->normalize(1212, false), 1212],
            [$this->serializer->normalize(1212, null), 1212],
            [$this->serializer->normalize(1212, []), 1212],
            [$this->serializer->normalize(new stdClass(), true), new stdClass()],
            [$this->serializer->normalize(new stdClass(), false), new stdClass()],
            [$this->serializer->normalize(new stdClass(), ''), new stdClass()],
            [$this->serializer->normalize(new stdClass(), '0'), new stdClass()],
            [$this->serializer->normalize(new stdClass(), 'fuck'), new stdClass()],
            [$this->serializer->normalize(new stdClass(), 111), new stdClass()],
            [$this->serializer->normalize(new TestClass(), true), new TestClass()],
            [$this->serializer->normalize(new TestClass(), false), new TestClass()],
            [$this->serializer->normalize(new TestClass(), ''), new TestClass()],
            [$this->serializer->normalize(new TestClass(), '0'), new TestClass()],
            [$this->serializer->normalize(new TestClass(), 'fuck'), new TestClass()],
            [$this->serializer->normalize(new TestClass(), 111), new TestClass()],
            [$this->serializer->normalize(null, TestClass::class), new TestClass()],
            [$this->serializer->normalize([], TestClass::class), new TestClass()],
            [$this->serializer->normalize(1, TestClass::class), new TestClass()],
            [$this->serializer->normalize('qwe', TestClass::class), new TestClass()],
            [$this->serializer->normalize(null, new TestClass()), new TestClass()],
            [$this->serializer->normalize([], new TestClass()), new TestClass()],
            [$this->serializer->normalize(1, new TestClass()), new TestClass()],
            [$this->serializer->normalize('qwe', new TestClass()), new TestClass()],
        ];
    }

    /**
     * @dataProvider defaultBehaviorProvider
     * @param $convert
     * @param $expected
     * @throws PHPUnit_Framework_Exception
     * @throws PHPUnit_Framework_ExpectationFailedException
     */
    public function testDefaultBehavior($convert, $expected)
    {
        $this->assertEquals($convert, $expected);
    }

    public function testSerializeCollection()
    {
        $collection = new TestCollectionClass();

        $object1 = $this->getAnonymousObject();
        $object2 = $this->getAnonymousObject();
        $object3 = $this->getAnonymousObject();

        $object1->setA('a1');
        $object1->getC()->setF('f1');

        $object2->setA('a2');
        $object2->addB('b21');
        $object2->addB('b21');
        $object2->addB('b21');
        $object2->addB('b21');
        $object2->getC()->setF('f2');
        $object2->setIsX(false);

        $object3->setA('a3');
        $object3->addB('b31');
        $object3->addB('b31');
        $object3->addB('b31');
        $object3->addB('b31');
        $object3->getC()->setF('f3');
        $object3->setIsX(true);

        $object1->addB($object2);
        $object2->addB($object3);

        $collection->add($object1);
        $collection->set($object2->getA(), $object2);
        $collection->set($object3->getB(), $object3);
        $json       = $this->serializer->serialize($collection);
        $jsonFilled = $this->serializer->jsonSignificant($collection);
        $this->assertEquals(
            $json,
            '{"0":{"a":"a1","b":[{"a":"a2","b":["b21","b21","b21","b21",{"a":"a3","b":["b31","b31","b31","b31"],"c":{"f":"f3"},"isX":true,"json":null}],"c":{"f":"f2"},"isX":false,"json":null}],"c":{"f":"f1"},"isX":null,"json":null},"a2":{"a":"a2","b":["b21","b21","b21","b21",{"a":"a3","b":["b31","b31","b31","b31"],"c":{"f":"f3"},"isX":true,"json":null}],"c":{"f":"f2"},"isX":false,"json":null},"a3":{"a":"a3","b":["b31","b31","b31","b31"],"c":{"f":"f3"},"isX":true,"json":null}}'
        );
        $this->assertEquals(
            $jsonFilled,
            '{"0":{"a":"a1","b":[{"a":"a2","b":["b21","b21","b21","b21",{"a":"a3","b":["b31","b31","b31","b31"],"c":{"f":"f3"},"isX":true}],"c":{"f":"f2"},"isX":false}],"c":{"f":"f1"}},"a2":{"a":"a2","b":["b21","b21","b21","b21",{"a":"a3","b":["b31","b31","b31","b31"],"c":{"f":"f3"},"isX":true}],"c":{"f":"f2"},"isX":false},"a3":{"a":"a3","b":["b31","b31","b31","b31"],"c":{"f":"f3"},"isX":true}}'
        );
    }

    public function testCollectionToArray()
    {
        $collection = new TestCollectionClass();
        $object1    = $this->getAnonymousObject();
        $object2    = $this->getAnonymousObject();
        $object2->setA('a2');

        $object1->setA('a');
        $object1->addB('b1');
        $object1->addB('b1');
        $object1->addB('b1');
        $object1->addB('b1');
        $object1->setC($object2);
        $object1->setIsX(false);

        $collection->add($object1);
        $collection->add($object2);
        $array       = $this->serializer->normalize($collection);
        $arrayFilled = $this->serializer->normalize($collection, null, Serializer::ONLY_FILLED);
        $this->assertEquals(
            [
                ['a' => 'a', 'b' => ['b1', 'b1', 'b1', 'b1'], 'c' => ['a' => 'a2', 'b' => null, 'isX' => null, 'json' => null, 'c' => ['f' => null]], 'isX' => false, 'json' => null],
                ['a' => 'a2', 'b' => null, 'isX' => null, 'json' => null, 'c' => ['f' => null]],
            ],
            $array
        );
        $this->assertEquals(
            [
                ['a' => 'a', 'b' => ['b1', 'b1', 'b1', 'b1'], 'c' => ['a' => 'a2'], 'isX' => false],
                ['a' => 'a2'],
            ],
            $arrayFilled
        );
    }

    public function testArrayToCollection()
    {
        $fixture = [
            0      => ['a' => 'a1', 'b' => 'qw1', 'isX' => 'fa1', 'c' => ['f' => 111]],
            'qwee' => ['a' => 'a2', 'b' => 'qw2', 'isX' => 'fa2', 'c' => ['f' => 222]],
            '1'    => ['a' => 'a3', 'b' => 'qw3', 'isX' => 'fa3', 'c' => ['f' => 333]],
            ['a' => 'a4', 'b' => 'qw4', 'isX' => 'fa4', 'c' => ['f' => 444]],
        ];
        /**
         * @var TestCollectionClass $collection
         */
        $collection = $this->serializer->normalize($fixture, TestCollectionClass2::class);
        $this->assertEquals(array_keys($collection->getElements()), [0, 'qwee', 1, 2]);
        $this->assertEquals($collection->getElements()[0]->getA(), 'a1');
        $this->assertEquals($collection->getElements()[0]->getB(), 'qw1');
        $this->assertEquals($collection->getElements()[0]->isX(), 'fa1');
        $this->assertEquals($collection->getElements()[0]->getC()->getF(), 111);

        $this->assertEquals($collection->getElements()['qwee']->getA(), 'a2');
        $this->assertEquals($collection->getElements()[1]->getA(), 'a3');
        $this->assertEquals($collection->getElements()[2]->getA(), 'a4');
    }

    public function testCollectionToCollection()
    {
        $collection1 = new TestCollectionClass();
        $collection2 = new TestCollectionClass2();

        $object1 = $this->getAnonymousObject();
        $object1->setA(1);
        $object1->addB('11');
        $object1->addB('11');
        $object1->addB('11');
        $object2 = $this->getAnonymousObject();
        $object2->setA(2);
        $object2->addB('22');
        $object2->addB('22');
        $object2->addB('22');
        $object2->setIsX(true);
        $object3 = $this->getAnonymousObject();
        $object3->setA(3);
        $object3->addB('33');
        $object3->addB('33');
        $object3->addB('33');
        $object3->setIsX(false);
        $object3->getC()->setF(33);


        $collection1->set($object1->getA(), $object1);
        $collection1->set($object2->getA(), $object2);
        $collection1->set($object3->getA(), $object3);
        $collection3 = $this->serializer->normalize($collection1, $collection2);
        $this->assertEquals($collection3->getElements()[1]->getA(), 1);
        $this->assertEquals($collection3->getElements()[2]->getA(), 2);
        $this->assertEquals($collection3->getElements()[3]->getA(), 3);
    }

    public function testCollectionToCollectionWithDifferentClasses()
    {
        $collection1 = new TestCollectionClass();
        $collection2 = new TestCollectionClass3();

        $object1 = $this->getAnonymousObject();
        $object1->setA(1);
        $object1->addB('11');
        $object1->addB('11');
        $object1->addB('11');
        $object2 = $this->getAnonymousObject();
        $object2->setA(2);
        $object2->addB('22');
        $object2->addB('22');
        $object2->addB('22');
        $object2->setIsX(true);
        $object3 = $this->getAnonymousObject();
        $object3->setA(3);
        $object3->addB('33');
        $object3->addB('33');
        $object3->addB('33');
        $object3->setIsX(false);
        $object3->getC()->setF(33);


        $collection1->set($object1->getA(), $object1);
        $collection1->set($object2->getA(), $object2);
        $collection1->set($object3->getA(), $object3);
        $collection3 = $this->serializer->normalize($collection1, $collection2);
        $this->assertInstanceOf(TestClassChild::class, $collection3->getElements()[1]);

        $this->assertEquals($collection3->getElements()[1]->getA(), 1);
        $this->assertEquals($collection3->getElements()[2]->getA(), 2);
        $this->assertEquals($collection3->getElements()[3]->getA(), 3);
    }

    public function testArrayWithJsonInObject()
    {
        $object = new TestClassWithJsonObject();
        $object->setA(5);
        $object->getJson()->setA(4);
        $object->getJson()->setB(3);
        $object->getJson()->setJson('{"s":"34"}');
        $array = [
            'a'    => 5,
            'json' => [
                'a'    => 4,
                'b'    => 3,
                'json' => '{"s":"34"}',
            ],
        ];
        /**
         * @var $entity TestClassWithJsonObject
         */
        $entity = $this->serializer->normalize($array, TestClassWithJsonObject::class);
        $this->assertEquals($object, $entity);
    }

    public function testArrayToStdClass()
    {
        $array               = [
            'a'    => 5,
            'json' => [
                'a'    => 4,
                'b'    => 3,
                'json' => '{"s":"34"}',
                'c'    => [
                    [
                        'a' => 5,
                    ],
                ],
            ],
        ];
        $object              = new stdClass();
        $object->a           = 5;
        $object->json        = new stdClass();
        $object->json->a     = 4;
        $object->json->b     = 3;
        $object->json->json  = '{"s":"34"}';
        $child               = new stdClass();
        $child->a            = 5;
        $object->json->c     = new stdClass();
        $i                   = 0;
        $object->json->c->$i = $child;
        $entity              = $this->serializer->normalize($array, new stdClass());
        $this->assertEquals($object, $entity);

    }

    public function testStdClassToObject()
    {
        $std             = new stdClass();
        $std->a          = 5;
        $std->json       = new stdClass();
        $std->json->z    = 4;
        $std->json->json = '{"s":"34"}';

        $object = new TestClassWithJsonObject();
        $object->setA(5);
        $object->getJson()->setZ(4);
        $object->getJson()->setJson('{"s":"34"}');
        $entity = $this->serializer->normalize($std, TestClassWithJsonObject::class);
        $this->assertEquals($object, $entity);
    }

    public function testStdClassWithUnAToObject()
    {
        $std             = new stdClass();
        $std->a          = 5;
        $std->json       = new stdClass();
        $std->json->z    = 4;
        $std->json->aq   = 3;
        $std->json->json = '{"s":"34"}';

        $object = new TestClassWithJsonObject();
        $object->setA(5);
        $object->getJson()->setZ(4);
        $object->getJson()->setJson('{"s":"34"}');
        $entity = $this->serializer->normalize($std, TestClassWithJsonObject::class);
        $this->assertEquals($object, $entity);
    }

    public function testStdClassToArray()
    {
        $std             = new stdClass();
        $std->a          = 5;
        $std->json       = new stdClass();
        $std->json->z    = 4;
        $std->json->aq   = 3;
        $std->json->json = '{"s":"34"}';
        $entity          = $this->serializer->normalize($std);
        $this->assertEquals($entity, ['a' => 5, 'json' => ['z' => 4, 'aq' => 3, 'json' => '{"s":"34"}']]);
    }

    public function testObjectWithJsonToArray()
    {
        $object = new TestClass();
        $object->setA(5);
        $object->setB(3);
        $object->setJson('{"s":"34"}');
        $entity = $this->serializer->normalize($object, null, Serializer::ONLY_FILLED);
        $this->assertEquals($entity, ['a' => 5, 'b' => 3, 'json' => '{"s":"34"}']);
    }

    public function testSnakeNormalizeArrayToObject()
    {
        $fixture = ['_prop1' => 1, '_prop2_prop' => 2, '_prop3_prop_' => 3, 'prop4' => 4];
        /**
         * @var $obj TestClass3
         */
        $obj = $this->serializer->normalize($fixture, TestClass3::class);
        $this->assertEquals($obj->getProp1(), null);
        $this->assertEquals($obj->getProp2Prop(), null);
        $this->assertEquals($obj->getProp3Prop(), null);
        $this->assertEquals($obj->getProp4(), 4);
        $obj = $this->serializer->normalize($fixture, TestClass3::class, Serializer::CAMEL_FORCE);
        $this->assertEquals($obj->getProp1(), 1);
        $this->assertEquals($obj->getProp2Prop(), 2);
        $this->assertEquals($obj->getProp3Prop(), 3);
        $this->assertEquals($obj->getProp4(), 4);
    }

    public function testSnakeNormalizeObjectToArray()
    {
        $obj = new TestClass3();
        $obj->setProp1(1);
        $obj->setProp2Prop(2);
        $obj->setProp3Prop(3);
        $obj->setProp4(4);
        $array = $this->serializer->normalize($obj);
        $this->assertEquals($array['_prop1'], 1);
        $this->assertEquals($array['_prop2_prop'], 2);
        $this->assertEquals($array['_prop3_prop_'], 3);
        $this->assertEquals($array['prop4'], 4);

        $array = $this->serializer->normalize($obj, null, Serializer::CAMEL_FORCE);
        $this->assertEquals($array['prop1'], 1);
        $this->assertEquals($array['prop2Prop'], 2);
        $this->assertEquals($array['prop3Prop'], 3);
        $this->assertEquals($array['prop4'], 4);
    }

    public function testArrayToListsOfListsOfLists()
    {
        $fixtures = [
            [
                [
                    [
                        'a' => 1,
                    ],
                    [
                        'a' => 'test',
                    ],
                ],
                [
                    [
                        'a' => 'test3',
                    ],
                ],
            ],
            [
                [
                    [
                        'a' => 'test2',
                    ],
                ],
            ],
        ];
        /**
         * @var $lists TestList
         */
        $lists = $this->serializer->normalize($fixtures, TestList::class);
        $this->assertEquals($lists->get(0)->get(0)->get(0)->getA(), 1);
        $this->assertEquals($lists->get(0)->get(0)->get(1)->getA(), 'test');
        $this->assertEquals($lists->get(1)->get(0)->get(0)->getA(), 'test2');
        $this->assertEquals($lists->get(0)->get(1)->get(0)->getA(), 'test3');
    }

    public function testListsOfListsOfListsToArray()
    {
        $fixtures = [
            [
                [
                    [
                        'a' => 1,
                    ],
                    [
                        'a' => 'test',
                    ],
                ],
                [
                    [
                        'a' => 'test3',
                    ],
                ],
            ],
            [
                [
                    [
                        'a' => 'test2',
                    ],
                ],
            ],
        ];


        /**
         * @var $lists TestList
         */
        $lists = $this->serializer->normalize($fixtures, TestList::class);
        $this->assertEquals($lists->get(0)->get(0)->get(0)->getA(), 1);
        $this->assertEquals($lists->get(0)->get(0)->get(1)->getA(), 'test');
        $this->assertEquals($lists->get(1)->get(0)->get(0)->getA(), 'test2');
        $this->assertEquals($lists->get(0)->get(1)->get(0)->getA(), 'test3');

        $lists = $this->serializer->normalize($lists, null, Serializer::ONLY_FILLED);

        $this->assertEquals($lists, $fixtures);
    }


    public function testStrictFullPackJsonToObject()
    {
        $object = SerializerFixtures::getObjectBigDataEntityStrict();
        $object->setListData(new BigDataListStrict());
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());

        $this->assertEquals($this->serializer->normalize(SerializerFixtures::getDataJson(), BigDataEntityStrict::class), $object);

        $list = new BigDataListStrict();
        $list->add($object);

        $this->assertEquals($this->serializer->normalize('[' . SerializerFixtures::getDataJson() . ']', BigDataListStrict::class), $list);
    }

    /**
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws PropertyWithUnknownTypeException
     */
    public function testStrictFullPackObjectToJson()
    {
        $object = SerializerFixtures::getObjectBigDataEntityStrict();
        $object->setListData(new BigDataListStrict());
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());
        $list = new BigDataListStrict();
        $list->add($object);

        $this->assertEquals($this->serializer->serialize($list), '[' . SerializerFixtures::getDataJson() . ']');
    }

    /**
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws PropertyWithUnknownTypeException
     */
    public function testStrictFullPackObjectToObject()
    {
        $objectStrict = SerializerFixtures::getObjectBigDataEntityStrict();
        $objectStrict->setListData(new BigDataListStrict());
        $objectStrict->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());
        $objectStrict->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());
        $objectStrict->getListData()->add(SerializerFixtures::getObjectBigDataEntityStrict());

        $object = $this->serializer->entityFill($objectStrict, BigDataEntity::class);
        $object = $this->serializer->entityFill($object, BigDataEntityStrict::class);

        $this->assertEquals($object, $objectStrict);

        $listStrict = new BigDataListStrict();
        $listStrict->add($objectStrict);

        $list = $this->serializer->entityFill($listStrict, BigDataList::class);
        $list = $this->serializer->entityFill($list, BigDataListStrict::class);

        $this->assertEquals($list, $listStrict);


        $object = SerializerFixtures::getObjectBigDataEntity();
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntity());
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntity());
        $object->getListData()->add(SerializerFixtures::getObjectBigDataEntity());

        $objectStrict = $this->serializer->entityFill($object, BigDataEntityStrict::class);
        $objectStrict = $this->serializer->entityFill($objectStrict, BigDataEntity::class);

        $this->assertEquals($object, $objectStrict);

        $list = new BigDataList();
        $list->add($object);

        $listStrict = $this->serializer->entityFill($list, BigDataListStrict::class);
        $listStrict = $this->serializer->entityFill($listStrict, BigDataList::class);

        $this->assertEquals($list, $listStrict);

    }

    /**
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws PropertyWithUnknownTypeException
     */
    public function testBigDataEntityStrictWithoutConstructor()
    {
        $objectStrict = $this->serializer->normalize(SerializerFixtures::getDataJson(), BigDataEntityStrictWithoutConstructor::class);
        $json         = $this->serializer->serialize($objectStrict);
        $this->assertEquals(SerializerFixtures::getDataJson(), $json);

        $objectStrict = $this->serializer->normalize(SerializerFixtures::getBigDataJson(), BigDataListStrictWithoutConstructor::class);
        $json         = $this->serializer->serialize($objectStrict);
        $this->assertEquals(SerializerFixtures::getBigDataJson(), $json);
    }

    /**
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws PropertyWithUnknownTypeException
     */
    public function testBigDataEntityStrictWithoutConstructorStrict()
    {
        $objectStrict = $this->serializer->entityFill(SerializerFixtures::getDataJson(), BigDataEntityStrictWithoutConstructor::class);
        $json         = $this->serializer->serialize($objectStrict);
        $this->assertEquals(SerializerFixtures::getDataJsonStrict(), $json);
    }

    /**
     * @throws EntityIsNotDescribedException
     * @throws EntityIsNotChosenException
     * @throws PropertyWithUnknownTypeException
     */
    public function testMigrationEntity()
    {
        $data = [
            'string' => 'qwerty',
            'int'    => 12345,
            'isBool' => true,
            'array'  => [1 => 2, 2 => ['qwe', 'ads']],
            'float'  => 1.232,
            'object' => [
                'string' => 'qwerty',
                'int'    => 12345,
                'isBool' => true,
                'array'  => [1 => 2, 2 => ['qwe', 'ads']],
                'float'  => 1.232,
                'object' => null,
                'float0' => 2.1111,
            ],
            'float0' => 2.1111,
        ];

        /**
         * @var $entity TestMigrationClass1
         */
        $entity = $this->serializer->normalize($data, TestMigrationClass1::class, Serializer::REWRITABLE | Serializer::FORCE_TYPE);

        /**
         * @var $entity1 TestMigrationClass2
         */
        $entity1 = $this->serializer->normalize($entity, TestMigrationClass2::class, Serializer::REWRITABLE | Serializer::FORCE_TYPE | Serializer::MIGRATION);

        $this->assertEquals($entity->getString(), 'qwerty');
        $this->assertEquals($entity1->getString1(), $entity->getString());
        $this->assertEquals($entity1->getObject1()->getString1(), $entity->getObject()->getString());

        $this->assertEquals($entity->getInt(), '12345');
        $this->assertEquals($entity1->getInt1(), $entity->getInt());
        $this->assertEquals($entity1->getInt(), null);
        $this->assertEquals($entity1->getObject1()->getInt1(), $entity->getObject()->getInt());

        $this->assertEquals($entity->isBool(), true);
        $this->assertEquals($entity1->isBool(), false);
        $this->assertEquals($entity->getObject()->isBool(), true);
        $this->assertEquals($entity1->getObject1()->isBool(), false);


        $this->assertEquals($entity->getArray(), [1 => 2, 2 => ['qwe', 'ads']]);
        $this->assertEquals($entity1->getArray(), $entity->getArray());
        $this->assertEquals($entity1->getObject1()->getArray(), $entity->getObject()->getArray());

        $this->assertEquals($entity->getFloat(), 1.232);
        $this->assertEquals($entity1->getFloat(), $entity->getFloat());
        $this->assertEquals($entity1->getObject1()->getFloat(), $entity->getObject()->getFloat());


        $this->assertEquals($entity->getFloat0(), 2.1111);
        $this->assertEquals($entity1->getFloat0(), $entity->getFloat0());
        $this->assertEquals($entity1->getObject1()->getFloat0(), $entity->getObject()->getFloat0());

        $this->assertEquals($entity->getObject()->getObject(), null);
        $this->assertEquals($entity1->getObject1()->getObject1(), $entity->getObject()->getObject());
    }

    /**
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws PropertyWithUnknownTypeException
     */
    public function testDataWithJsonToObjectAndBack()
    {
        $entity = $this->serializer->normalize(['a' => 2, 'json' => ['x' => 3, 'y' => 5, 'z' => 10]], TestClassStrictWithJsonObject::class);
        $this->assertInstanceOf(TestClassStrictWithJsonObject::class, $entity);
        $this->assertInstanceOf(TestClassChild::class, $entity->getJson());

        $entity->setJson($this->serializer->normalize($entity->getJson()));
        $array = $this->serializer->normalize($entity, null, Serializer::ARRAY_WITHOUT_JSON);
        $this->assertIsArray($array['json']);
        $this->assertEquals($array['json']['x'], 3);
        $this->assertEquals($array['json']['z'], 10);
        $array1 = $this->serializer->normalize($entity);
        $this->assertEquals($array1['json'], '{"x":3,"z":10}');
    }

    public function testListWithArrayToArray()
    {
        $obj = new TestClassWithArrays();
        $obj->setData(
            [
                $this->serializer->normalize(['a' => 1], TestClass::class),
                $this->serializer->normalize(['a' => 2], TestClass::class),
            ]
        );
        $array = $this->serializer->normalize($obj);
        $this->assertTrue($array['data'][0]['a'] === 1);
        $this->assertTrue($array['data'][1]['a'] === 2);

        $obj = new TestClassStrictWithArrays();
        $obj->setData(
            [
                $this->serializer->normalize(['a' => 1], TestClass::class),
                $this->serializer->normalize(['a' => 2], TestClass::class),
            ]
        );
        $array = $this->serializer->normalize($obj);
        $this->assertTrue($array['data'][0]['a'] === 1);
        $this->assertTrue($array['data'][1]['a'] === 2);
    }

    public function testAddableStrict()
    {
        $array = ['data' => [1, 2, 3, 4]];
        $obj   = new TestClassStrictWithArrays();
        $obj->setData([5, 6, 7, 8, 9, 10]);
        $obj = $this->serializer->normalize($array, $obj, Serializer::ADDABLE);
        $this->assertEquals($obj->getData(), [1, 2, 3, 4, 9, 10]);

        $array = ['data' => [1, 2, 3, 4]];
        $obj   = new TestClassWithArrays();
        $obj->setData([5, 6, 7, 8, 9, 10]);
        $obj = $this->serializer->normalize($array, $obj, Serializer::ADDABLE);
        $this->assertEquals($obj->getData(), [5, 6, 7, 8, 9, 10, 1, 2, 3, 4]);
    }

    public function testNullableStrict()
    {
        $array = ['qwe' => null];
        $obj   = new TestClassStrictWithArrays();
        $obj->setQwe('asdfg');

        $obj = $this->serializer->normalize($array, $obj, Serializer::REWRITABLE);
        $this->assertEquals($obj->getQwe(), 'asdfg');

        $obj = $this->serializer->normalize($array, $obj, Serializer::NULLABLE);
        $this->assertEquals($obj->getQwe(), 'asdfg');

        $obj = $this->serializer->normalize($array, $obj, Serializer::REWRITABLE | Serializer::NULLABLE);
        $this->assertEquals($obj->getQwe(), null);
    }

    public function testCamelStrict()
    {
        $fixture = ['_prop1' => 1, '_prop2_prop' => 2, '_prop3_prop_' => 3, 'prop4' => 4];
        $error   = false;
        try {
            $obj = $this->serializer->normalize($fixture, TestClass3Strict::class);
        } catch (\Throwable $exception) {
            $error = true;
        }
        $this->assertTrue($error);
        $obj = $this->serializer->normalize($fixture, TestClass3::class, Serializer::CAMEL_FORCE);
        $this->assertEquals($obj->getProp1(), 1);
        $this->assertEquals($obj->getProp2Prop(), 2);
        $this->assertEquals($obj->getProp3Prop(), 3);
        $this->assertEquals($obj->getProp4(), 4);
    }

    /**
     * @return TestClass
     */
    private function getAnonymousObject(): TestClass
    {
        return new TestClass();
    }
}