Serializer служит для упаковки и распаковки данных из разных состояний

#Простые преобразования
+ #####Ассоциативный массив -> Обьект
```php
$array = ['a'=>1,'b'=>'b','c'=>['isSt'=>false];

/** Можно указывать класс */
(new Serializer())->normalize($array,Example::class);

/** Может принимать готовый обьект */
(new Serializer())->normalize($array,new Example());
```
+ #####Массив массивов -> Лист Обьектов
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
+ #####Json -> Массив
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
+ #####Json -> Обьект
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
+ #####Обьект -> Массив
```php
/** поведение идентично */
(new Serializer())->normalize($object,null);
(new Serializer())->normalize($object);
(new Serializer())->normalize($object,[]);
```
+ #####Обьект -> Json
```php
/** поведение идентично */
(new Serializer())->serialize($object);
(new Serializer())->serialize($object,'json');
```
#Преобразования с флагами
+ ####Ассоциативный массив -> Обьект
**Serializer::ADDABLE** - является дефолтным флагом при заполнении данных.

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
**Serializer::REWRITABLE** - нужно указывать если хотите перезаписать поля, при преобразовании.

В реализации с PropertyStrictAccessInterface перезаписывает только поля (TYPE_BOOL, TYPE_INT, TYPE_FLOAT, TYPE_STRING)
```php
$obj = new Example();
$obj->setA('Qwerty');
$obj->setB(12345);
$obj = (new Serializer())->normalize(['a'=>'asd', 'b' => 321 ], $obj, Serializer::REWRITABLE);
$obj->getA() === 'asd';
$obj->getB() === 321;
```
**Serializer::NULLABLE** - нужно указывать если хотите перезаписать поля в null, работает в связке с Serializer::REWRITABLE
```php
$obj = new Example();
$obj->setA('Qwerty');
$obj->setB(12345);
$obj = (new Serializer())->normalize(['a'=>null, 'b' => null ], $obj, Serializer::REWRITABLE | Serializer::NULLABLE);
$obj->getA() === null;
$obj->getB() === null;
```
**Serializer::CAMEL_FORCE** - указывает что свойства нужно преобразовать в CamelCase

Если не указать этот параметр при преобразовании подобных данных - произойдет Fatal Error
```php
$data = ['_prop1' => 1, '_prop2_prop' => 2, '_prop3_prop_' => 3, 'prop4' => 4];

$obj = (new Serializer())->normalize(['a'=>null, 'b' => null ], $obj, Serializer::REWRITABLE | Serializer::NULLABLE);
$obj->getA() === null;
$obj->getB() === null;
```



```php
```

