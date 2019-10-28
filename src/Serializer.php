<?php

namespace Kluatr\Serializer;

use Kluatr\Serializer\Error\EntityIsNotChosen;
use Kluatr\Serializer\Error\EntityIsNotDescribed;
use Kluatr\Serializer\Error\InvalidRegistrationOfProperty;
use Kluatr\Serializer\Error\PropertyWithUnknownType;
use stdClass;

/**
 * Class Serializer
 * @package Helpers
 */
class Serializer
{

    /**
     * NULLABLE - Обнулять параметрами из источника
     * REWRITABLE - Перезаписывать параметрами из источника
     * ADDABLE - Добавлять параметрами источника, если субьект имеет, что-то у себя
     * CAMEL_FORCE - Превращает ключи в camelCase
     * ONLY_FILLED - Удаление null полей
     * ARRAY_WITHOUT_JSON - служебный ключ, который свидетельствует что внутри сущности может быть json и его нужно упаковать слив в один json
     * CLEAR_INDEX_KEY - очищает ключи-индексы из листов, когда преобразуешь в массив/json
     * RENAME_PROPERTIES - говорим сущности, что нужно замапить согласно интерфейсу
     * MIGRATION -  заменит RENAME_PROPERTIES, переименование между обьектами по мапе
     */
    public const  ADDABLE            = 0b0000000001;
    public const  REWRITABLE         = 0b0000000010;
    public const  NULLABLE           = 0b0000000100;
    public const  CAMEL_FORCE        = 0b0000001000;
    public const  ONLY_FILLED        = 0b0000010000;
    public const  ARRAY_WITHOUT_JSON = 0b0000100000;
    public const  CLEAR_INDEX_KEY    = 0b0001000000;
    public const  FORCE_TYPE         = 0b0010000000;
    public const  RENAME_PROPERTIES  = 0b0100000000;
    public const  MIGRATION          = 0b1000000000;

    public const TYPE_INT    = 0b00000001;
    public const TYPE_STRING = 0b00000010;
    public const TYPE_FLOAT  = 0b00000100;
    public const TYPE_JSON   = 0b00001000;
    public const TYPE_BOOL   = 0b00010000;
    public const TYPE_OBJECT = 0b00100000;
    public const TYPE_NULL   = 0b01000000;
    public const TYPE_ARRAY  = 0b10000000;

    private static $entityCache = [];

    /**
     * Десериализует данные
     * @param       $source
     * @param       $subject
     * @param int $flags
     * @return mixed
     */
    public function normalize($source, $subject = null, int $flags = self::ADDABLE)
    {
        try {
            return $this->performNormalize($source, $subject, $flags);
        } catch (\Throwable $exception) {
            /**
             * todo::возможно стоит ошибку упаковать в какой-нибудь ServiceError{}
             */
            return null;
        }
    }

    /**
     * @param        $source
     * @param string $type
     * @param int $flags
     * @return mixed
     */
    public function serialize($source, string $type = 'json', int $flags = 0)
    {
        try {
            return $this->performSerialize($source, $type, $flags);
        } catch (\Throwable $exception) {
            /**
             * todo::возможно стоит ошибку упаковать в какой-нибудь ServiceError{}
             */
            return null;
        }
    }

    /**
     * @param $source
     * @param int $flags
     * @return mixed
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    public function jsonSignificant($source, int $flags = Serializer::ONLY_FILLED)
    {
        return $this->performSerialize($source, 'json', $flags);
    }

    /**
     * @param ContainsCollectionInterface $collection
     * @param string $property
     * @return string|null
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws InvalidRegistrationOfProperty
     */
    public function getGetterByCollection(ContainsCollectionInterface $collection, string $property)
    {
        $class           = $collection->getClass();
        $subjectCacheKey = $this->getKeyOfCachedClass($class, self::ADDABLE);

        if (!$this->issetCache($subjectCacheKey)) {
            $subjectCacheKey = $this->indexClass(new $class(), self::ADDABLE);
        }

        return static::$entityCache[$subjectCacheKey][$property]['getter'];
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function setMethod(string $key): string
    {
        return 'set' . ucfirst($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getMethod(string $key): string
    {
        return 'get' . ucfirst($key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function addMethod(string $key): string
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
     * @param $data
     * @param $entity
     * @return mixed
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    public function entityFill($data, $entity)
    {
        return $this->performNormalize($data, $entity, Serializer::REWRITABLE | Serializer::CAMEL_FORCE | Serializer::ADDABLE | Serializer::FORCE_TYPE);
    }

    /**
     * @param ContainsCollectionInterface $collection
     * @param array $keys
     * @return ContainsCollectionInterface
     * @deprecated
     * Фильтруем лист по ключам
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

    /**
     * @param $source
     * @param null $subject
     * @param int $flags
     * @return array|mixed|null
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function performNormalize($source, $subject = null, int $flags = self::ADDABLE)
    {
        switch (true) {
            case is_object($subject):
                switch (true) {
                    /** object -> object{PropertyStrictAccessInterface} */
                    case $source instanceOf PropertyStrictAccessInterface:
                        $this->objectToObjectStrict($source, $subject, $flags);
                        break;
                    /** array -> stdClass */
                    case $subject instanceOf StdClass && is_array($source):
                        $this->arrayToStdClass($source, $subject, $flags);
                        break;
                    /** stdClass -> array -> object */
                    case $source instanceOf StdClass:
                        $tmpArray = [];
                        $this->stdClassToArray($source, $tmpArray, $flags);
                        $this->performNormalize($tmpArray, $subject, $flags);
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
                        switch (true) {
                            case $subject instanceOf ContainsCollectionInterface:
                                $this->arrayToCollectionObject($source, $subject, $flags);
                                break;
                            case $subject instanceOf PropertyStrictAccessInterface:
                                $this->arrayToObjectStrict($source, $subject, $flags);
                                break;
                            default:
                                $this->arrayToObject($source, $subject, $flags);
                        }
                        break;
                    /** json -> object */
                    case $this->isJson($source):
                        $this->performNormalize(json_decode($source, true), $subject, $flags);
                        break;
                }
                break;
            /** Создает класс по имени и рекурсивно вызываем */
            case is_string($subject):
                if (class_exists($subject)) {
                    $subject = $this->performNormalize($source, new $subject(), $flags);
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
                            switch (true) {
                                /** Если элемент массива - массив, и он определен в субьекте - то лезем внутрь */
                                case is_array($element) && isset($subject[$key]):
                                    $subject[$key] = $this->performNormalize($element, $subject[$key], $flags);
                                    break;
                                /** если внутри массива обьект */
                                case is_object($element):
                                case $element instanceOf PropertyAccessInterface :
                                case $element instanceof PropertyStrictAccessInterface:
                                case $element instanceof ContainsCollectionInterface:
                                    $subject[$key] = $this->performNormalize($element, [], $flags);
                                    break;
                                /** стандартное поведение */
                                default:
                                    $subject[$key] = $element;

                            }
                        }
                        break;
                    /** PropertyStrictAccessInterface -> array */
                    case is_object($source) && $source instanceOf PropertyStrictAccessInterface:
                        if ($subject === null) {
                            $subject = [];
                        }
                        $this->objectToArrayStrict($source, $subject, $flags);
                        break;
                    /** PropertyAccessInterface -> array */
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
     * @param $source
     * @param string $type
     * @param int $flags
     * @return array|false|mixed|string|null
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function performSerialize($source, string $type = 'json', int $flags = 0)
    {
        switch (true) {
            case is_string($source) && $this->isJson($source):
                return $source;
            /**
             * превращаем в массив, и проваливаемся в следующий кейс.
             */
            case is_object($source):
            case $source instanceOf PropertyAccessInterface:
            case $source instanceOf PropertyStrictAccessInterface:
            case $source instanceOf ContainsCollectionInterface:
                $source = $this->performNormalize($source, null, $flags | self::ARRAY_WITHOUT_JSON);
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
                foreach ($source as $key => $value) {
                    if (is_object($value)) {
                        $source[$key] = $this->performNormalize($value, null, $flags | self::ARRAY_WITHOUT_JSON);
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
     * Циклом нормализует данные в обьекте(кеп)
     * @param array $source
     * @param ContainsCollectionInterface $subject
     * @param $flags
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function arrayToCollectionObject(array $source, ContainsCollectionInterface $subject, $flags): void
    {
        foreach ($source as $key => $property) {
            $subject->set($key, $this->performNormalize($property, $subject->getClass(), $flags));
        }
    }

    /**
     * Переливает обьект в обьект
     * @param PropertyAccessInterface $source
     * @param mixed $subject
     * @param int $flags
     * @return void
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function objectToObject(PropertyAccessInterface $source, &$subject, int $flags = self::ADDABLE): void
    {
        $sourceCacheKey  = $this->indexClass($source, $flags);
        $subjectCacheKey = $this->indexClass($subject, $flags);

        foreach ($source->getProperties() as $property) {

            if (!array_key_exists($property, static::$entityCache[$subjectCacheKey])) {
                continue;
            }

            $setMethod    = static::$entityCache[$sourceCacheKey][$property]['setter'];
            $addMethod    = static::$entityCache[$sourceCacheKey][$property]['adder'];
            $sourceGetter = static::$entityCache[$sourceCacheKey][$property]['getter'];

            $subjectGetter = static::$entityCache[$subjectCacheKey][$property]['getter'];


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
                $this->performNormalize($source->$sourceGetter(), $subject->$subjectGetter(), $flags);
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
     * @param PropertyStrictAccessInterface|PropertyAccessInterface $object
     * @param int $flags
     * @return string
     * @throws EntityIsNotDescribed
     * @throws InvalidRegistrationOfProperty
     */
    private function indexClass($object, int $flags)
    {
        $sourceClassName = get_class($object);
        $sourceCacheKey  = $this->getKeyOfCachedClass($sourceClassName, $flags);
        if (!$this->issetCache($sourceCacheKey)) {
            switch (true) {
                case $object instanceOf PropertyStrictAccessInterface:
                    foreach ($object->getPropertiesStrict() as $nameProperty => $propertyInfo) {
                        if ($this->isRenameProperties($flags) && $object instanceof RenameMappingInterface && isset($object->getRenameMapping()[$nameProperty])) {
                            $nameProperty = $object->getRenameMapping()[$nameProperty];
                        }
                        $currentNameProperty = $this->isCamelForce($flags) ? $this->toCamel($nameProperty) : $nameProperty;
                        if (empty($propertyInfo['type'])) {
                            throw new InvalidRegistrationOfProperty();
                        }
                        static::$entityCache[$sourceCacheKey][$nameProperty] = [
                            'getter' => $this->getGetterByType($currentNameProperty, $propertyInfo['type']),
                            'setter' => $this->setMethod($currentNameProperty),
                            'adder'  => $this->addMethod($currentNameProperty),
                            'info'   => $propertyInfo,
                        ];
                    }
                    break;
                case $object instanceOf PropertyAccessInterface:
                    foreach ($object->getProperties() as $nameProperty) {
                        if ($this->isRenameProperties($flags) && $object instanceof RenameMappingInterface && isset($object->getRenameMapping()[$nameProperty])) {
                            $nameProperty = $object->getRenameMapping()[$nameProperty];
                        }
                        $currentNameProperty = $this->isCamelForce($flags) ? $this->toCamel($nameProperty) : $nameProperty;

                        static::$entityCache[$sourceCacheKey][$nameProperty] = [
                            'getter'          => $this->getExistingGetter($object, $currentNameProperty),
                            'getterToCamel'   => $this->getExistingGetter($object, $this->toCamel($nameProperty)),
                            'setter'          => $this->setMethod($currentNameProperty),
                            'adder'           => $this->addMethod($currentNameProperty),
                            'propertyToCamel' => $this->toCamel($nameProperty),
                        ];
                    }
                    break;
                default:
                    throw new EntityIsNotDescribed('Класс не реализует никакой удовлетворительный интерфейс преобразования - ' . $sourceClassName);
            }
        }

        return $sourceCacheKey;
    }


    /**
     * @param string $cacheKey
     * @return bool
     */
    private function issetCache(string $cacheKey): bool
    {
        return isset(static::$entityCache[$cacheKey]);
    }

    /**
     * @param $className
     * @param int $flags
     * @return string
     */
    private function getKeyOfCachedClass($className, int $flags): string
    {
        return $className . $flags;
    }

    /**
     * @param PropertyStrictAccessInterface $source
     * @param PropertyStrictAccessInterface|PropertyAccessInterface $subject
     * @param int $flags
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function objectToObjectStrict(PropertyStrictAccessInterface $source, &$subject, int $flags = self::ADDABLE)
    {
        $sourceCacheKey  = $this->indexClass($source, $flags);
        $subjectCacheKey = $this->indexClass($subject, $flags);


        foreach ($source->getPropertiesStrict() as $nameProperty => $propertyInfo) {

            $sourceGetter = static::$entityCache[$sourceCacheKey][$nameProperty]['getter'];

            if ($this->isMigration($flags) && $source instanceof MigrationEntityInterface && array_key_exists(get_class($subject), $source->getEntityRelations())) {
                $migrateMap = $source->getEntityRelations()[get_class($subject)];
                if (isset($migrateMap[$nameProperty])) {
                    $nameProperty = $migrateMap[$nameProperty];
                }
            }

            if (!array_key_exists($nameProperty, static::$entityCache[$subjectCacheKey])) {
                continue;
            }

            $subjectGetter = static::$entityCache[$subjectCacheKey][$nameProperty]['getter'];
            $setMethod     = static::$entityCache[$subjectCacheKey][$nameProperty]['setter'];
            $addMethod     = static::$entityCache[$subjectCacheKey][$nameProperty]['adder'];

            switch (true) {
                case static::TYPE_BOOL & $propertyInfo['type']:
                case static::TYPE_INT & $propertyInfo['type']:
                case static::TYPE_FLOAT & $propertyInfo['type']:
                case static::TYPE_STRING & $propertyInfo['type']:
                    if ($this->isNullable($flags) === false && $source->$sourceGetter() === null) {
                        continue 2;
                    }
                    if ($this->isRewritable($flags) === false && $subject->$subjectGetter() !== null) {
                        continue 2;
                    }
                    $subject->$setMethod($source->$sourceGetter());
                    continue 2;
                case static::TYPE_OBJECT & $propertyInfo['type']:
                    if (static::TYPE_NULL & $propertyInfo['type']) {
                        if ($source->$sourceGetter() === null) {
                            continue 2;
                        }
                        if ($subject->$subjectGetter() === null) {
                            $subject->$setMethod(new $propertyInfo['class']());
                        }
                    }

                    $this->performNormalize($source->$sourceGetter(), $subject->$subjectGetter(), $flags);
                    continue 2;
                case static::TYPE_ARRAY & $propertyInfo['type']:
                    if ($subject->$subjectGetter() === null) {
                        if (static::TYPE_NULL & $propertyInfo['type']) {
                            $subject->$setMethod([]);
                        }
                        $subject->$setMethod($source[$nameProperty]);
                    } else {
                        foreach ($source->$sourceGetter() as $k => $v) {
                            $subject->$addMethod($k, $v);
                        }
                    }
                    continue 2;
            }
        }
    }

    /**
     * @param array $source
     * @param PropertyStrictAccessInterface $subject
     * @param int $flags
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws EntityIsNotChosen
     * @throws InvalidRegistrationOfProperty
     */
    private function arrayToObjectStrict(array $source, PropertyStrictAccessInterface &$subject, int $flags = self::ADDABLE): void
    {
        $subjectCacheKey = $this->indexClass($subject, $flags);

        foreach ($subject->getPropertiesStrict() as $nameProperty => $propertyInfo) {

            if (!isset($source[$nameProperty])) {
                continue;
            }

            $subjectGetter = static::$entityCache[$subjectCacheKey][$nameProperty]['getter'];

            $setMethod = static::$entityCache[$subjectCacheKey][$nameProperty]['setter'];
            $addMethod = static::$entityCache[$subjectCacheKey][$nameProperty]['adder'];

            switch (true) {
                case static::TYPE_BOOL & $propertyInfo['type']:
                case static::TYPE_INT & $propertyInfo['type']:
                case static::TYPE_FLOAT & $propertyInfo['type']:
                case static::TYPE_STRING & $propertyInfo['type']:
                    if ($source[$nameProperty] === null && $this->isNullable($flags) === false) {
                        continue 2;
                    }
                    if ($this->isRewritable($flags) === false && $subject->$subjectGetter() !== null) {
                        continue 2;
                    }
                    $subject->$setMethod($this->isForceType($flags) ? $this->setType($propertyInfo['type'], $source[$nameProperty]) : $source[$nameProperty]);
                    continue 2;
                case static::TYPE_OBJECT & $propertyInfo['type']:
                    if (static::TYPE_NULL & $propertyInfo['type']) {
                        if (empty($source[$nameProperty])) {
                            continue 2;
                        }
                        if ($subject->$subjectGetter() === null && isset($propertyInfo['class'])) {
                            $subject->$setMethod(new $propertyInfo['class']());
                        }
                    }
                    $this->performNormalize($source[$nameProperty], $subject->$subjectGetter(), $flags);
                    continue 2;
                case static::TYPE_ARRAY & $propertyInfo['type']:
                    if ($subject->$subjectGetter() === null) {
                        if (static::TYPE_NULL & $propertyInfo['type']) {
                            $subject->$setMethod([]);
                        }
                        $subject->$setMethod($source[$nameProperty]);
                    } else {
                        foreach ($source[$nameProperty] as $k => $v) {
                            $subject->$addMethod($k, $v);
                        }
                    }
                    continue 2;
            }
        }
    }

    /**
     * @param array $source
     * @param mixed $subject
     * @param int $flags
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function arrayToObject(array $source, &$subject, int $flags = self::ADDABLE): void
    {

        $subjectCacheKey = $this->indexClass($subject, $flags);

        foreach ($source as $property => $value) {

            if (!array_key_exists($property, static::$entityCache[$subjectCacheKey])) {
                continue;
            }

            $setMethod     = static::$entityCache[$subjectCacheKey][$property]['setter'];
            $addMethod     = static::$entityCache[$subjectCacheKey][$property]['adder'];
            $subjectGetter = static::$entityCache[$subjectCacheKey][$property]['getter'];


            /**
             * Если элемент сорса массив , а элемент того уровня коллекция - упаковываем в коллекцию
             */
            if (is_array($value) && $subjectGetter !== null && $subject->$subjectGetter() instanceof ContainsCollectionInterface) {
                foreach ((array)$value as $subKey => $subValue) {
                    $subject->$subjectGetter()->set($subKey, $this->performNormalize($subValue, $subject->$subjectGetter()->getClass(), $flags));
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
                if (is_array($value) || is_object($value) || ($this->isJsonProperty($subject, $property) && $this->isJson($value))) {
                    $this->performNormalize($value, $subject->$subjectGetter($value), $flags);
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
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function arrayToStdClass(array $source, stdClass &$subject, int $flags = self::ADDABLE): void
    {
        foreach ($source as $key => $item) {
            if (is_array($item)) {
                $subject->$key = $this->performNormalize($item, new stdClass(), $flags);
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
     * @param PropertyStrictAccessInterface $source
     * @param array $subject
     * @param int $flags
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function objectToArrayStrict(PropertyStrictAccessInterface $source, array &$subject = [], int $flags = self::ADDABLE): void
    {
        $sourceCacheKey = $this->indexClass($source, $flags);

        foreach ($source->getPropertiesStrict() as $nameProperty => $propertyInfo) {

            $sourceGetter = static::$entityCache[$sourceCacheKey][$nameProperty]['getter'];

            if ($this->isSerializeFilled($flags) && $source->$sourceGetter() === null) {
                continue;
            }

            if (!array_key_exists($nameProperty, $subject)) {
                $subject[$nameProperty] = null;
            }

            switch (true) {
                case static::TYPE_BOOL & $propertyInfo['type']:
                case static::TYPE_INT & $propertyInfo['type']:
                case static::TYPE_FLOAT & $propertyInfo['type']:
                case static::TYPE_STRING & $propertyInfo['type']:
                    /**
                     * Простой сет свойства, если они совпадают по имени
                     */
                    if ($this->isNullable($flags) === false && $source->$sourceGetter() === null) {
                        continue 2;
                    }
                    if ($subject[$nameProperty] !== null && $this->isRewritable($flags) === false) {
                        continue 2;
                    }
                    $subject[$nameProperty] = $source->$sourceGetter();
                    continue 2;
                case static::TYPE_JSON & $propertyInfo['type'] && $this->isArrayWithJson($flags):
                    $subject[$nameProperty] = $this->performSerialize($source->$sourceGetter(), 'json', $flags | Serializer::ONLY_FILLED);
                    continue 2;
                case static::TYPE_OBJECT & $propertyInfo['type']:
                    $subject[$nameProperty] = $this->performNormalize($source->$sourceGetter(), $subject[$nameProperty], $flags);
                    if ($this->isSerializeFilled($flags) && $subject[$nameProperty] === []) {
                        unset($subject[$nameProperty]);
                    }
                    continue 2;
                case static::TYPE_ARRAY & $propertyInfo['type']:

                    /**
                     * Разбор ситуации если свойство обьекта массив в котором могут быть обьекты
                     */
                    $sourceData = $source->$sourceGetter();

                    if (static::TYPE_NULL & $propertyInfo['type'] && $sourceData === null) {
                        continue 2;
                    }

                    /**
                     * $fakeSubject не пуст если внутри $sourceData лежат обьекты
                     */
                    $fakeSubject = [];
                    foreach ($sourceData as $key => &$data) {
                        if (is_object($data)) {
                            if ($data instanceof PropertyAccessInterface) {
                                $fakeSubject[$key] = [];
                                $this->objectToArray($data, $fakeSubject[$key]);
                            } elseif ($data instanceof PropertyStrictAccessInterface) {
                                $fakeSubject[$key] = [];
                                $this->objectToArrayStrict($data, $fakeSubject[$key]);
                            }

                        }
                    }
                    $sourceData = array_replace($sourceData, $fakeSubject);

                    /**
                     * Если субьект заполнен и есть разрешение
                     */
                    if (!empty($subject[$nameProperty])) {
                        if ($this->isAddable($flags) === false && count($sourceData) > 0) {
                            continue 2;
                        }
                        $subject[$nameProperty] = array_merge($sourceData, $subject[$nameProperty]);
                    } else {
                        $subject[$nameProperty] = array_merge($sourceData);
                    }
                    continue 2;
            }

        }
    }

    /**
     * @param PropertyAccessInterface $source
     * @param array $subject
     * @param int $flags
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function objectToArray(PropertyAccessInterface $source, array &$subject = [], int $flags = self::ADDABLE): void
    {
        $sourceCacheKey = $this->indexClass($source, $flags);

        foreach ($source->getProperties() as $property) {

            $getMethod = static::$entityCache[$sourceCacheKey][$property]['getterToCamel'];

            $property = $this->isCamelForce($flags) ? static::$entityCache[$sourceCacheKey][$property]['propertyToCamel'] : $property;

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
                $subject[$property] = $this->performSerialize($source->$getMethod(), 'json', $flags | Serializer::ONLY_FILLED);
                continue;
            }

            /**
             * Рекурсивно вызывается,если св-во subject является обьектом
             */
            if (method_exists($source, $getMethod) &&
                (is_array($source->$getMethod()) || is_object($source->$getMethod()))
            ) {
                $subject[$property] = $this->performNormalize($source->$getMethod(), $subject[$property], $flags);
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

    /**
     * @param ContainsCollectionInterface $source
     * @param array $subject
     * @param int $flags
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function collectionToArray(ContainsCollectionInterface $source, array &$subject, int $flags)
    {
        if ($this->isClearIndexKey($flags)) {
            $i = 0;
            foreach ($source->getElements() as $element) {
                $subject[$i++] = $this->performNormalize($element, [], $flags);
            }
        } else {
            $subject = $this->performNormalize($source->getElements(), $subject, $flags);
        }
    }


    /**
     * @param string $name
     * @param $type
     * @return string
     */
    private function getGetterByType(string $name, $type): string
    {
        switch (true) {
            case $type & static::TYPE_BOOL:
                return $name;
            default:
                return $this->getMethod($name);
        }
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
     * @param int $flags
     * @return bool
     */
    private function isForceType(int $flags): bool
    {
        return (bool)(static::FORCE_TYPE & $flags);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isRenameProperties(int $flags): bool
    {
        return (bool)(static::RENAME_PROPERTIES & $flags);
    }

    /**
     * @param int $flags
     * @return bool
     */
    private function isMigration(int $flags): bool
    {
        return (bool)(static::MIGRATION & $flags);
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
     * @throws EntityIsNotChosen
     * @throws EntityIsNotDescribed
     * @throws PropertyWithUnknownType
     * @throws InvalidRegistrationOfProperty
     */
    private function collectionToCollection(ContainsCollectionInterface $source, ContainsCollectionInterface $subject, int $flags)
    {
        if (count($subject->getElements()) > 0 && !$this->isAddable($flags)) {
            return;
        }

        foreach ($source->getElements() as $key => $element) {
            if ($source->getClass() === $subject->getClass()) {
                $subject->set($key, $element);
            } else {
                $subject->set($key, $this->performNormalize($element, $subject->getClass(), $flags));
            }
        }
    }

    /**
     * @param $object
     * @param string $property
     * @return bool
     */
    private function isJsonProperty($object, string $property): bool
    {
        if ($object instanceof HasJsonPropertiesInterface) {
            return (bool)is_int(array_search($property, $object->getJsonProperties()));
        }

        return false;
    }

    /**
     * @param string $type
     * @param $value
     * @return bool|float|int|string
     * @throws PropertyWithUnknownType
     */
    private function setType(string $type, $value)
    {
        switch (true) {
            case $type & static::TYPE_STRING:
                return (string)($value);
            case $type & static::TYPE_BOOL:
                return (bool)($value);
            case $type & static::TYPE_INT:
                return (int)($value);
            case $type & static::TYPE_FLOAT:
                return (float)($value);
        }
        throw new PropertyWithUnknownType('Неизвестный тип данных -' . $type);
    }
}
