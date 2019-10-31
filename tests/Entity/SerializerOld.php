<?php

namespace Test\Lib\Serializer\Entity;


use Lib\Serializer\ContainsCollectionInterface;
use Lib\Serializer\Error\EntityIsNotChosenException;
use Lib\Serializer\HasJsonPropertiesInterface;
use Lib\Serializer\PropertyAccessInterface;
use Lib\Serializer\RenameMappingInterface;
use Lib\Serializer\Serializer;
use stdClass;

/**
 * @deprecated
 * Class Serializer
 * @package Helpers
 */
class SerializerOld
{

    /**
     * NULLABLE - Обнулять параметрами из источника
     * REWRITABLE - Перезаписывать параметрами из источника
     * ADDABLE - Добавлять параметрами источника, если субьект имеет, что-то у себя
     * CAMEL_FORCE - Превращает ключи в camelCase
     * ONLY_FILLED - Удаление null полей
     * ARRAY_WITHOUT_JSON - служебный ключ, который свидетельствует что внутри сущности может быть json и его нужно упаковать слив в один json
     * CLEAR_INDEX_KEY - очищает ключи-индексы из листов, когда преобразуешь в массив/json
     */
    public const  ADDABLE            = 0b00000001;
    public const  REWRITABLE         = 0b00000010;
    public const  NULLABLE           = 0b00000100;
    public const  CAMEL_FORCE        = 0b00001000;
    public const  ONLY_FILLED        = 0b00010000;
    private const ARRAY_WITHOUT_JSON = 0b00100000;
    public const  CLEAR_INDEX_KEY    = 0b01000000;

    /**
     * Десериализует данные
     * @param       $source
     * @param       $subject
     * @param int $flags
     * @return mixed
     */
    public function normalize($source, $subject = null, int $flags = self::ADDABLE)
    {
        switch (true) {
            case is_object($subject):
                switch (true) {
                    /** array -> stdClass */
                    case $subject instanceOf StdClass && is_array($source):
                        $this->arrayToStdClass($source, $subject, $flags);
                        break;
                    /** stdClass -> array -> object */
                    case $source instanceOf StdClass:
                        $tmpArray = [];
                        $this->stdClassToArray($source, $tmpArray, $flags);
                        $this->normalize($tmpArray, $subject, $flags);
                        break;
                    /** object -> object{PropertyAccessInterface} */
                    case $source instanceOf PropertyAccessInterface:
                        $this->objectToObject($source, $subject, $flags);
                        break;
                    /** object -> object{ContainsCollectionInterface} */
                    case $subject instanceOf ContainsCollectionInterface && $source instanceOf ContainsCollectionInterface:
                        $this->collectionToCollection($source, $subject, $flags);
                        break;
                    /**
                     * array -> collection object
                     * array -> object
                     */
                    case is_array($source):
                        if ($subject instanceOf ContainsCollectionInterface) {
                            $this->arrayToCollectionObject($source, $subject, $flags);
                        } else {
                            $this->arrayToObject($source, $subject, $flags);
                        }
                        break;
                    /** json -> object */
                    case $this->isJson($source):
                        $this->normalize(json_decode($source, true), $subject, $flags);
                        break;
                }
                break;
            /** Создает класс по имени и рекурсивно вызываем */
            case is_string($subject):
                if (class_exists($subject)) {
                    $subject = $this->normalize($source, new $subject(), $flags);
                } else {
                    $subject = $source;
                }
                break;
            case is_array($subject) || $subject === null:
                switch (true) {
                    /** stdClass -> array */
                    case is_object($source) && $source instanceOf StdClass:
                        $this->stdClassToArray($source, $subject, $flags);
                        break;
                    /**
                     * Если обьект преобразования коллекция
                     * Подменяем сорс внутренними элементами
                     */
                    case  is_object($source) && $source instanceof ContainsCollectionInterface:
                        if ($subject === null) {
                            $subject = [];
                        }
                        $this->collectionToArray($source, $subject, $flags);
                        break;
                    /** array -> array */
                    case is_array($source):
                        $subject = [];
                        $i       = 0;
                        foreach ($source as $key => $element) {
                            if ($this->isClearIndexKey($flags)) {
                                $key = $i++;
                            }
                            /** Если элемент массива - массив, и он определен в субьекте - то лезем внутрь */
                            if (is_array($element) && isset($subject[$key])) {
                                $subject[$key] = $this->normalize($element, $subject[$key], $flags);
                                /** если внутри массива обьект */
                            } elseif (is_object($element) && ($element instanceOf PropertyAccessInterface || $element instanceof  ContainsCollectionInterface)) {
                                $subject[$key] = $this->normalize($element, [], $flags);
                                /** стандартное поведение */
                            } else {
                                $subject[$key] = $element;
                            }
                        }
                        break;
                    /** object -> array */
                    case is_object($source) && $source instanceOf PropertyAccessInterface:
                        if ($subject === null) {
                            $subject = [];
                        }
                        $this->objectToArray($source, $subject, $flags);
                        break;
                    /** array -> json */
                    case !is_object($source) && $this->isJson($source):
                        $subject = json_decode($source, true);
                        break;
                    default:
                        $subject = $source;
                }
                break;
            default:
                $subject = $source;
        }


        return $subject;
    }

    /**
     * @param        $source
     * @param string $type
     * @param int $flags
     * @return mixed
     */
    public function serialize($source, string $type = 'json', int $flags = 0)
    {
        switch (true) {
            case is_string($source) && $this->isJson($source):
                return $source;
            /**
             * превращаем в массив, и проваливаемся в следующий кейс.
             */
            case is_object($source) && ($source instanceOf PropertyAccessInterface || $source instanceOf ContainsCollectionInterface):
                $source = $this->normalize($source, null, $flags | self::ARRAY_WITHOUT_JSON);
            case is_array($source):
                if ($this->isSerializeFilled($flags)) {
                    $source = $this->arrayFilterRecursive(
                        $source,
                        function($el)
                        {
                            return $el !== null;
                        }
                    );
                    if (count($source) === 0) {
                        return null;
                    }
                }
                if ($type === 'json') {
                    $source = json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                break;
        }

        return $source;
    }

    /**
     * @param $source
     * @param int $flags
     * @return mixed
     */
    public function jsonSignificant($source, int $flags = Serializer::ONLY_FILLED)
    {
        return $this->serialize($source, 'json', $flags);
    }

    /**
     * Циклом нормализует данные в обьекте(кеп)
     * @param array $source
     * @param ContainsCollectionInterface $subject
     * @param $flags
     * @throws EntityIsNotChosenException
     */
    private function arrayToCollectionObject(array $source, ContainsCollectionInterface $subject, $flags): void
    {
        foreach ($source as $key => $property) {
            $subject->set($key, $this->normalize($property, $subject->getClass(), $flags));
        }
    }

    /**
     * Переливает обьект в обьект
     * @param PropertyAccessInterface $source
     * @param mixed $subject
     * @param int $flags
     * @return void
     */
    private function objectToObject(PropertyAccessInterface $source, &$subject, int $flags = self::ADDABLE): void
    {
        foreach ($source->getProperties() as $property) {
            if ($source instanceof RenameMappingInterface && isset($source->getRenameMapping()[$property])) {
                $property = $source->getRenameMapping()[$property];
            } else {
                if ($this->isCamelForce($flags)) {
                    $property = $this->toCamel($property);
                }
            }
            $setMethod     = $this->setMethod($property);
            $addMethod     = $this->addMethod($property);
            $sourceGetter  = $this->getExistingGetter($source, $property);
            $subjectGetter = $this->getExistingGetter($subject, $property);

            /**
             * Добавляет элементы если свойство в объекте - это массив
             */
            if (is_array($source->$sourceGetter()) &&
                method_exists($subject, $addMethod)) {
                if ($this->isAddable($flags) === false && count($source->$sourceGetter()) > 0) {
                    continue;
                }

                foreach ((array)$source->$sourceGetter() as $subValue) {
                    $subject->$addMethod($subValue);
                }
                continue;
            }
            /**
             * Рекурсивно вызывается,если св-во subject является обьектом
             */
            if (method_exists($source, $sourceGetter) &&
                method_exists($subject, $subjectGetter) &&
                is_object($subject->$subjectGetter()) &&
                (is_array($source->$sourceGetter()) || is_object($source->$sourceGetter()))
            ) {
                $this->normalize($source->$sourceGetter(), $subject->$subjectGetter(), $flags);
                continue;
            }
            /**
             * Простой сет свойства, если они совпадают по имени
             */
            if (method_exists($subject, $setMethod) && $subjectGetter !== null) {
                if ($this->isNullable($flags) === false && $source->$sourceGetter() === null) {
                    continue;
                }
                if ($this->isRewritable($flags) === false && $subject->$subjectGetter() !== null) {
                    continue;
                }
                $subject->$setMethod($source->$sourceGetter());
                continue;
            }
        }
    }

    /**
     * @param array $source
     * @param mixed $subject
     * @param int $flags
     */
    private function arrayToObject(array $source, &$subject, int $flags = self::ADDABLE): void
    {
        foreach ($source as $key => $value) {

            $currentKey = $this->isCamelForce($flags) ? $this->toCamel($key) : $key;

            $setMethod     = $this->setMethod($currentKey);
            $addMethod     = $this->addMethod($currentKey);
            $subjectGetter = $this->getExistingGetter($subject, $currentKey);

            /**
             * Если элемент сорса массив , а элемент того уровня коллекция - упаковываем в коллекцию
             */
            if (is_array($value) && $subjectGetter !== null && $subject->$subjectGetter() instanceof ContainsCollectionInterface) {
                foreach ((array)$value as $subKey => $subValue) {
                    $subject->$subjectGetter()->set($subKey, $this->normalize($subValue, $subject->$subjectGetter()->getClass(), $flags));
                }
                continue;
            }

            /**
             */
            if (is_array($value) && method_exists($subject, $addMethod)) {
                if ($this->isAddable($flags) === false && count($value) > 0) {
                    continue;
                }
                foreach ($value as $subValue) {
                    $subject->$addMethod($subValue);
                }
                continue;
            }

            /**
             * Если поле определено как json -> object | array -> object | object -> object
             */
            if ($subjectGetter !== null && is_object($subject->$subjectGetter())) {
                if (is_array($value) || is_object($value) || ($this->isJsonProperty($subject, $key) && $this->isJson($value))) {
                    $this->normalize($value, $subject->$subjectGetter($value), $flags);
                    continue;
                }
            }

            /**
             * Простой сет свойства, если они совпадают по имени
             */
            if (method_exists($subject, $setMethod)) {
                if ($value === null && $this->isNullable($flags) === false) {
                    continue;
                }
                if ($this->isRewritable($flags) === false && $subject->$subjectGetter() !== null) {
                    continue;
                }
                $subject->$setMethod($value);
                continue;
            }
        }
    }

    /**
     * @param array $source
     * @param stdClass $subject
     * @param int $flags
     */
    private function arrayToStdClass(array $source, stdClass &$subject, int $flags = self::ADDABLE): void
    {
        foreach ($source as $key => $item) {
            if (is_array($item)) {
                $subject->$key = $this->normalize($item, new stdClass(), $flags);
            } else {
                $subject->$key = $item;
            }
        }
    }

    /**
     * @param stdClass $source
     * @param $subject
     * @param int $flags
     */
    private function stdClassToArray($source, &$subject, int $flags = self::ADDABLE)
    {
        if ($source instanceof stdClass) {
            $source = get_object_vars($source);
        }
        foreach ($source as $property => $item) {
            if ($this->isCamelForce($flags)) {
                $property = $this->toCamel($property);
            }

            if (!isset($subject[$property])) {
                $subject[$property] = null;
            }

            if ($item instanceof stdClass || is_array($item)) {
                $this->stdClassToArray($item, $subject[$property], $flags);
            } else {
                $subject[$property] = $item;
            }
        }
    }

    /**
     * @param PropertyAccessInterface $source
     * @param array $subject
     * @param int $flags
     */
    private function objectToArray(PropertyAccessInterface $source, array &$subject = [], int $flags = self::ADDABLE): void
    {
        foreach ($source->getProperties() as $property) {

            $property = $this->isCamelForce($flags) ? $this->toCamel($property) : $property;

            $getMethod = $this->getExistingGetter($source, $this->toCamel($property));

            if ($this->isSerializeFilled($flags) && $source->$getMethod() === null) {
                continue;
            }

            if (!array_key_exists($property, $subject)) {
                $subject[$property] = null;
            }

            if (is_array($source->$getMethod())) {

                /**
                 * Разбор ситуации если свойство обьекта массив в котором могут быть обьекты
                 */
                $sourceData = $source->$getMethod();
                /**
                 * $fakeSubject не пуст если внутри $sourceData лежат обьекты
                 */
                $fakeSubject = [];
                foreach ($sourceData as $key => &$data) {
                    if (is_object($data) && $data instanceof PropertyAccessInterface) {
                        $fakeSubject[$key] = [];
                        $this->objectToArray($data, $fakeSubject[$key]);
                    }
                }
                $sourceData = array_replace($sourceData, $fakeSubject);

                /**
                 * Если субьект заполнен и есть разрешение
                 */
                if (!empty($subject[$property])) {
                    if ($this->isAddable($flags) === false && count($sourceData) > 0) {
                        continue;
                    }
                    $subject[$property] = array_merge($sourceData, $subject[$property]);
                } else {
                    $subject[$property] = array_merge($sourceData);
                }
                continue;
            }

            if ($this->isJsonProperty($source, $property) && $this->isArrayWithJson($flags)) {
                $subject[$property] = $this->serialize($source->$getMethod(), 'json', $flags | Serializer::ONLY_FILLED);
                continue;
            }

            /**
             * Рекурсивно вызывается,если св-во subject является обьектом
             */
            if (method_exists($source, $getMethod) &&
                (is_array($source->$getMethod()) || is_object($source->$getMethod()))
            ) {
                $subject[$property] = $this->normalize($source->$getMethod(), $subject[$property], $flags);
                if ($this->isSerializeFilled($flags) && $subject[$property] === []) {
                    unset($subject[$property]);
                }
                continue;
            }

            /**
             * Простой сет свойства, если они совпадают по имени
             */
            if ($this->isNullable($flags) === false && $source->$getMethod() === null) {
                continue;
            }
            if ($subject[$property] !== null && $this->isRewritable($flags) === false) {
                continue;
            }
            $subject[$property] = $source->$getMethod();
            continue;
        }
    }

    private function collectionToArray(ContainsCollectionInterface $source, array &$subject, int $flags)
    {
        if ($this->isClearIndexKey($flags)) {
            $i = 0;
            foreach ($source->getElements() as $element) {
                $subject[$i++] = $this->normalize($element, [], $flags);
            }
        } else {
            $subject = $this->normalize($source->getElements(), $subject, $flags);
        }
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function setMethod(string $key): string
    {
        return 'set' . ucfirst($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function getMethod(string $key): string
    {
        return 'get' . ucfirst($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function addMethod(string $key): string
    {
        return 'add' . ucfirst($key);
    }

    /**
     * @param $source
     * @param string $key
     * @return string
     */
    public function getExistingGetter($source, string $key): ?string
    {
        switch (true) {
            case preg_match('/^is[A-Z].*/', $key) && method_exists($source, $key):
                return $key;
            case method_exists($source, $this->getMethod($key)):
                return $this->getMethod($key);
            default:
                return null;
        }
    }

    /**
     * @param $data
     * @return bool
     */
    public function isJson($data): bool
    {
        $data = json_decode($data, true);

        return (json_last_error() === JSON_ERROR_NONE) && is_array($data);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isNullable(int $flags): bool
    {
        return (bool)(static::NULLABLE & $flags);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isRewritable(int $flags): bool
    {
        return (bool)(static::REWRITABLE & $flags);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isAddable(int $flags): bool
    {
        return (bool)(static::ADDABLE & $flags);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isCamelForce(int $flags): bool
    {
        return (bool)(static::CAMEL_FORCE & $flags);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isSerializeFilled(int $flags): bool
    {
        return (bool)(static::ONLY_FILLED & $flags);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isArrayWithJson(int $flags): bool
    {
        return (bool)(static::ARRAY_WITHOUT_JSON & ~$flags);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isClearIndexKey(int $flags): bool
    {
        return (bool)(static::CLEAR_INDEX_KEY & $flags);
    }

    /**
     * @param string $string
     * @return string
     */
    private function toCamel(string $string): string
    {
        $string    = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
        $string[0] = strtolower($string[0]);

        return $string;
    }

    /**
     * @param array $array
     * @param callable|null $callback
     * @return array
     */
    private function arrayFilterRecursive(array $array, callable $callback = null): array
    {
        $array = is_callable($callback) ? array_filter($array, $callback) : array_filter($array);
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->arrayFilterRecursive($value, $callback);
                if (count($value) === 0) {
                    unset($array[$key]);
                }
            }
        }

        return $array;
    }

    /**
     * @param ContainsCollectionInterface $source
     * @param ContainsCollectionInterface $subject
     * @param int $flags
     * @throws EntityIsNotChosenException
     */
    private function collectionToCollection(ContainsCollectionInterface $source, ContainsCollectionInterface $subject, int $flags)
    {
        $count = count($subject->getElements());
        if ($count > 0 && !$this->isAddable($flags)) {
            return;
        }

        foreach ($source->getElements() as $key => $element) {
            if ($source->getClass() === $subject->getClass()) {
                $subject->set($key, $element);
            } else {
                $subject->set($key, $this->normalize($element, $subject->getClass(), $flags));
            }
        }
    }

    /**
     * @param ContainsCollectionInterface $collection
     * @param string $property
     * @return array
     * @throws EntityIsNotChosenException
     * @deprecated использовать getColumn из AbstractList
     */
    public function getColumnByList(ContainsCollectionInterface $collection, string $property): array
    {
        $array = [];
        $class = $collection->getClass();
        if (new $class() instanceof PropertyAccessInterface) {
            $i = 0;
            foreach ($collection->getElements() as $value) {
                $array[$value->{$this->getExistingGetter($value, $property)}()] = $i++;
            }
        }

        return array_flip($array);
    }

    /**
     * Фильтруем лист по ключам
     * @param ContainsCollectionInterface $collection
     * @param array $keys
     * @return ContainsCollectionInterface
     */
    public function getFilteredList(ContainsCollectionInterface $collection, array $keys): ContainsCollectionInterface
    {
        /**
         * @var $newCollection ContainsCollectionInterface
         */
        $classList     = get_class($collection);
        $newCollection = new $classList();
        foreach ($keys as $key) {
            if ($collection->has($key)) {
                $newCollection->set($key, $collection->get($key));
            }
        }

        return $newCollection;
    }

    private function isJsonProperty($object, string $property): bool
    {
        if ($object instanceof HasJsonPropertiesInterface) {
            return (bool)is_int(array_search($property, $object->getJsonProperties()));
        }

        return false;
    }

    /**
     * @param $data
     * @param $entity
     * @return mixed
     */
    public function entityFill($data, $entity)
    {
        return $this->normalize($data, $entity, Serializer::REWRITABLE | Serializer::CAMEL_FORCE | Serializer::ADDABLE);
    }
}