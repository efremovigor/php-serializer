`Serializer` служит для упаковки и распаковки данных из разных состояний. 
Преобразовывать данные можно любой вложенности и структуры в `json`/`array`/`object`/`objectList`.
`Object` может быть вложен в другой `object`, в котором может быть `objectList`, преобразование в `json`/`array` и обратно - должно происходить без проблем
#Описание обьекта
Класс должен имплементить один из интерфейсов **`PropertyStrictAccessInterface`/`PropertyAccessInterface`**

Для того чтобы работать с листами нужно наследоваться от `EntityList`, либо имплементить `ContainsCollectionInterface`

**`EntityList`** - содержит набор фич для обработки данных листов таких как:

+ Работа с листом по странично
+ Сортировка внутри листа
+ Поиск внутри листа по значению/значениям
+ Переиндексация листа по полю
+ Получение колонки из листа


**`ContainsCollectionInterface`** - служит для листов данных

**`PropertyStrictAccessInterface`** - строгое описание обьекта

**`PropertyAccessInterface`** - описание полей обьекта(не рекомендуется)

###EntityList

Нужно просто указать какой класс будет внутри метода `getClass`, служит только при заполнении новыми элементами в лист на лету,
если этого не сделать будет ошибка при заполнении, при других действия проблема не должна возникнуть

###PropertyAccessInterface

Содержит метод `getProperties` - который должен отдавать массив свойств доступных для заполнения/преобразования
Нужно указать создать для каждого поля `getter` и `setter`.

####Пример
```php
public function getProperties(){
    'propertyNameInt',
    'propertyNameString',
    'isPropertyNameBool',
}

public function getPropertyNameInt(){
    return $this->propertyNameInt;
}
public function getPropertyNameString(){
    return $this->propertyNameString;
}
public function isPropertyNameBool(){
    return $this->isPropertyNameBool;
}

public function setPropertyNameInt($propertyNameInt){
    $this->propertyNameInt = $propertyNameInt;
}
public function setPropertyNameString($propertyNameString){
    $this->propertyNameString = $propertyNameString;
}
public function setIsPropertyNameBool($isPropertyNameBool){
    $this->isPropertyNameBool = $isPropertyNameBool;
}
```
###PropertyStrictAccessInterface

Условно строгий тип заполнения, в отличии от `PropertyAccessInterface` использует более простой алгоритм, который не прощает неправильного заполнения свойств и их методов

####типы данных

+ `TYPE_INT` 
+ `TYPE_STRING`
+ `TYPE_FLOAT` 
+ `TYPE_JSON`  
+ `TYPE_BOOL`  
+ `TYPE_OBJECT`
+ `TYPE_NULL`  
+ `TYPE_ARRAY` 

####Пример реализации метода
```php
    public function getPropertiesStrict(): array
    {
        return [
            'string1' => ['type' => Serializer::TYPE_STRING],
            'int1'    => ['type' => Serializer::TYPE_INT],
            'object1' => ['type' => Serializer::TYPE_JSON | Serializer::TYPE_OBJECT, 'class' => TestMigrationClass3::class],
            'int'     => ['type' => Serializer::TYPE_INT],
            'isBool'  => ['type' => Serializer::TYPE_BOOL],
            'array'   => ['type' => Serializer::TYPE_ARRAY],
            'float'   => ['type' => Serializer::TYPE_FLOAT | Serializer::TYPE_NULL],
            'float0'  => ['type' => Serializer::TYPE_FLOAT],
        ];
    }
```

Указывать можно несколько типов одновременно, сделано для таких случаев, когда у нас поле может быть не определено, т.е. иметь значение `null` и один типов `int`|`string`|`object`|`array` 

Так-же часто имеет смысл указывать одновременно `TYPE_JSON` + `TYPE_OBJECT`, это сделано для того, чтобы мы могли при заполнении обьекта с помощью массива налету преобразовать `json`, который находится внутри одного из свойств, и упаковать его в обьект, который либо уже существует, либо создать его на лету и так-же упаковать(для этого нужно указать свойство `class` и поле должно иметь флаг `TYPE_NULL`)

Геттеры и сеттеры нужно создавать согласно примеру `PropertyAccessInterface`

Если при заполнении указать флаг `Serializer::REWRITABLE` - геттеры игнорируются, иначе они проверяются что в свойстве ничего нет

#Простое использование
#####Если мы хотим заполнить обьект
```php
/** 
 * @param $data данные формата json/array/object 
 * @param $subject object|classname
*/

/** идентичные действия */
(new Serializer())->entityFill($data, $subject)
(new Serializer())->normalize($data, $subject, Serializer::REWRITABLE | Serializer::CAMEL_FORCE | Serializer::ADDABLE | Serializer::FORCE_TYPE);
```
#####Если мы хотим получить `array` из `object`
```php
/** 
 * @param $data данные формата object 
*/

/** идентичные действия */
$array = (new Serializer())->normalize($data);
$array = (new Serializer())->normalize($data,null);
$array = (new Serializer())->normalize($data,[]);
$array = (new Serializer())->normalize($data,$array);
```
#####Если мы хотим получить `json` из `object`|`array` 
```php
/** 
 * @param $data данные формата array 
*/

/** идентичные действия */
$array = (new Serializer())->serialize($data);
$array = (new Serializer())->serialize($data,'json');

/** это действия оставит только заполнение поля */
$array = (new Serializer())->jsonSignificant($data);
```
#Простые преобразования
+ #####Ассоциативный `array` -> `object`
```php
$array = ['a'=>1,'b'=>'b','c'=>['isSt'=>false];

/** Можно указывать класс */
(new Serializer())->normalize($array,Example::class);

/** Может принимать готовый обьект */
(new Serializer())->normalize($array,new Example());
```
+ #####Массив массивов -> `objectList`
```php
$array = [
    ['a'=>1,'b'=>'b','c'=>['isSt'=>false],
    ['a'=>2,'b'=>'b','c'=>['isSt'=>false],
    ['a'=>3,'b'=>'b','c'=>['isSt'=>false],
];

/** Можно указывать класс */
(new Serializer())->normalize($array,ExampleList::class);

/** Может принимать готовый обьект */
(new Serializer())->normalize($array,new ExampleList());
```
+ #####`json` -> `array`
```php
$json = [
 {"a":1,"b":"b","c":{"isSt":false}},
 {"a":1,"b":"b","c":{"isSt":false}},
 {"a":1,"b":"b","c":{"isSt":false}}
];
/** поведение идентично */
(new Serializer())->normalize($json);
(new Serializer())->normalize($json,null);
```
+ #####`json` -> `object`
```php
$json = [
 {"a":1,"b":"b","c":{"isSt":false}},
 {"a":1,"b":"b","c":{"isSt":false}},
 {"a":1,"b":"b","c":{"isSt":false}}
];

/** Можно указывать класс */
(new Serializer())->normalize($json,ExampleList::class);

/** Может принимать готовый обьект */
(new Serializer())->normalize($json,new ExampleList());
```
+ #####`object` -> `array`
```php
/** поведение идентично */
(new Serializer())->normalize($object,null);
(new Serializer())->normalize($object);
(new Serializer())->normalize($object,[]);
```
+ #####`object` -> `json`
```php
/** поведение идентично */
(new Serializer())->serialize($object);
(new Serializer())->serialize($object,'json');
```
+ #####`object` -> `object`
```php
$obj = new Example();
$obj->setA('Qwerty');
$obj->setB(12345);
$obj1 = new Example1();

(new Serializer())->normalize($obj,$obj1);
$obj1->getA() === $obj->getA();
$obj1->getB() === $obj->getB();

```
+ #####`objectList` -> `objectList`
```php
$list1 = new List();
$list->set('unicalId1',$obj);
$list->set('unicalId12',$obj);
$list->set('unicalId13',$obj);

$list2 = new List();
$list2->set('unicalId1111',$obj);

/**  $list3 будет содержать все элементы из обоих листов */
$list3 = (new Serializer())->normalize($list1,$list2);
```
+ #####`stdClass` -> `array`
```php
$obj = new stdClass();
$obj->a = 1;
$obj->b = 4;
$array = (new Serializer())->normalize(new stdClass);
$array === ['a'=>1,'b'=>4];
```
+ #####`array` -> `stdClass`
```php
/** какой-то stdClass */
$object = new stdClass();
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

/** превратится в подобный массив */
$array  === [
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

/** при подобном преобразовании */
$array = (new Serializer())->normalize(new stdClass);
```
+ #####`stdClass` -> `array` -> `object`
 Преобразование разбивается на этапы под капотом и делает стандартные действия

#Преобразования с флагами
**`Serializer::ADDABLE`** - является дефолтным флагом при заполнении данных.

Он указывает на то, что если свойство обьекта является массивом и оно уже содержит данные - то данные из `$array` по имени свойства будут перезаписаны по ключам

#####Если Example implemented PropertyAccessInterface
```php
$array = ['data'=>[1,2,3,4]];
$obj = new Example();
$obj->setData([5,6,7,8,9,10])
$obj = (new Serializer())->normalize($array, $obj, Serializer::ADDABLE);
$obj['data'] === [1,2,3,4,9,10,1,2,3,4];
```
#####Если Example implemented PropertyStrictAccessInterface
```php
$array = ['data'=>[1,2,3,4]];
$obj = new Example();
$obj->setData([5,6,7,8,9,10])
$obj = (new Serializer())->normalize($array, $obj, Serializer::ADDABLE);
$obj['data'] === [1,2,3,4,9,10];
```
**`Serializer::REWRITABLE`** - нужно указывать если хотите перезаписать поля, при преобразовании.

В реализации с `PropertyStrictAccessInterface` перезаписывает только поля (`TYPE_BOOL`, `TYPE_INT`, `TYPE_FLOAT`, `TYPE_STRING`)
```php
$obj = new Example();
$obj->setA('Qwerty');
$obj->setB(12345);
$obj = (new Serializer())->normalize(['a'=>'asd', 'b' => 321 ], $obj, Serializer::REWRITABLE);
$obj->getA() === 'asd';
$obj->getB() === 321;
```
**`Serializer::NULLABLE`** - нужно указывать если хотите перезаписать поля в `null`, работает в связке с `Serializer::REWRITABLE`
```php
$obj = new Example();
$obj->setA('Qwerty');
$obj->setB(12345);
$obj = (new Serializer())->normalize(['a'=>null, 'b' => null ], $obj, Serializer::REWRITABLE | Serializer::NULLABLE);
$obj->getA() === null;
$obj->getB() === null;
```
**`Serializer::CAMEL_FORCE`** - указывает что свойства нужно преобразовать в CamelCase

Если не указать этот параметр при преобразовании подобных данных - произойдет `Fatal Error`
```php
$data = ['_prop1' => 1, '_prop2_prop' => 2, '_prop3_prop_' => 3, 'prop4' => 4];

$obj = (new Serializer())->normalize($data, new Example(), Serializer::CAMEL_FORCE);
$obj->getProp1() === 1;
$obj->getProp2Prop() === 2;
$obj->getProp3Prop() === 3;
$obj->getProp4() === 4;
```
**`Serializer::ONLY_FILLED`** - при преобразовании `object` -> `array` -> `json`, отсеиваются поля, которые имеют значения `null`
```php
$obj = new Example();
$obj->setProperty1(2);
$obj->setProperty2('qwe');
$obj->setProperty3(true);
$obj->setProperty4(null);
$obj->setProperty5([]);
$array = (new Serializer())->normalize($obj, null, Serializer::ONLY_FILLED);
$array === ['property1' => 2, 'property2'=> 'qwe', 'property3' => true, 'property5' => [] ];
$json = (new Serializer())->serialize($obj, null, Serializer::ONLY_FILLED);
$json === '{"property1":2,"property2":"qwe","property3":true,"property5":[]},';
```
**`Serializer::ARRAY_WITHOUT_JSON`** -  нужно указывать когда у вас свойство указано типом - `TYPE_JSON` и вы хотите получить данные из обьекта не в `json`, а в массиве
```php

/** представим что Example содержит внутри в свйостве json - описанные обьект по формату из свойства json */
$objWith = (new Serializer())->normalize(['a' => 2, 'json' => {"x": 3, "z" : 10}], Example::class);

/** данные будут упакованы в его структуру */
$objWith->getJson() instanceof SomethingObject

$arrayWithJson = (new Serializer())->normalize($objWith)
$arrayWithoutJson = (new Serializer())->normalize($objWith, null, Serializer::ARRAY_WITHOUT_JSON)
```
**`Serializer::CLEAR_INDEX_KEY`** - если у нас обьект/массив, который содержит в листах внутри ключи - ключи будут очищенны и будет добавлены автоинкрементные ключи
```php
$list = new List();
$list->set('unicalId1',$obj);
$list->set('unicalId12',$obj);
$list->set('unicalId123',$obj);

$json = (new Serializer())->serialize($list, 'json', Serializer::CLEAR_INDEX_KEY);
$json === '[{...},{...},{...}]';
```
**`Serializer::MIGRATION`** - случит для миграции `obj.field` => `obj1.field` между обьектами, нужно в обьекте-источнике implemented `MigrationEntityInterface` и описать какие поля нужно изменить при переносе в методе `getEntityRelations`
```php

/** Example implemented MigrationEntityInterface */
public function getEntityRelations(): array
    {
        return [
            Example1::class => [
                'a' => 'c',
                'b' => 'd',
            ],
        ];
    }
*/

$obj = new Example();
$obj->setA('Qwerty');
$obj->setB(12345);
$obj1 = new Example1();

(new Serializer())->normalize($obj,$obj1,Serializer::MIGRATION);
$obj1->getA() === $obj->getC();
$obj1->getB() === $obj->getD();
```
**`Serializer::FORCE_TYPE`** - работает совместно с `PropertyStrictAccessInterface` преобразует типы согласно тому, как указано в описании обьекта


