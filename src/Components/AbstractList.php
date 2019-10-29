<?php

namespace Kluatr\Serializer\Components;

use Countable;
use Iterator;
use Kluatr\Serializer\ContainsCollectionInterface;
use Kluatr\Serializer\Error\EntityIsNotChosenException;
use Kluatr\Serializer\Error\EntityIsNotDescribedException;
use Kluatr\Serializer\Error\InvalidRegistrationOfPropertyException;
use Kluatr\Serializer\Serializer;
use OutOfBoundsException;

abstract class AbstractList implements Iterator, Countable
{

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    protected $elements = [];
    /**
     * @var Serializer
     */
    private static $serializer;

    /**
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        if (static::$serializer === null) {
            static::$serializer = new Serializer();
        }

        return static::$serializer;
    }

    /**
     * @param $element
     */
    public function add($element): void
    {
        $this->elements[$this->count()] = $element;
    }

    /**
     * @param $key
     * @param $element
     */
    public function set($key, $element): void
    {
        $this->elements[$key] = $element;
    }

    public function has($key): bool
    {
        return isset($this->elements[$key]);
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->elements[$key] ?? null;
    }

    /**
     * Return the current element
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->elements[$this->position];
    }

    /**
     * Move forward to next element
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element
     * @return mixed scalar on success, or null on failure.
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return isset($this->elements[$this->position]);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Rewind the Iterator to the first element
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Seeks to a position
     *
     * @param int $position
     * @return void
     * @throws OutOfBoundsException
     */
    public function seek($position): void
    {
        if (!isset($this->elements[$position])) {
            throw new OutOfBoundsException("invalid seek position ($position)");
        }
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function getFirstElement()
    {
        reset($this->elements);

        return $this->elements[key($this->elements)];
    }


    public function getLastElement()
    {
        end($this->elements);

        return $this->elements[key($this->elements)];
    }

    /**
     * @param array $elements
     */
    public function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

    /**
     * @param $key
     */
    public function removeElement($key)
    {
        unset($this->elements[$key]);
    }

    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * @param Pagination $pagination
     * @return $this
     */
    public function getPage(Pagination $pagination): self
    {
        $i     = 0;
        $page  = $pagination->getPage();
        $limit = $pagination->getLimit();

        /**
         * @var self $list
         */
        $list = new $this();
        foreach ($this->elements as $key => $element) {
            if ($i >= $page * $limit && $i < $page * $limit + $limit && $list->count() < $limit) {
                $list->set($key, $element);
            }
            if ($i > $page * $limit + $limit) {
                break;
            }
            $i++;
        }

        return $list;
    }

    /**
     * @param SortBy $sort
     * @return $this
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws InvalidRegistrationOfPropertyException
     */
    public function getSortedList(SortBy $sort): self
    {
        if ($this->isEmpty()) {
            return $this;
        }

        /**
         * @var self|ContainsCollectionInterface $list
         */
        $list = new $this();

        $method = static::getSerializer()->getGetterByCollection($list, $sort->getField());

        switch ($sort->getType()) {
            case 'string':
                $list->setElements($sort->sortString($this->elements, $method));
                break;
            case 'int':
                $list->setElements($sort->sortInteger($this->elements, $method));
                break;
        }

        return $list;
    }

    /**
     * Ищет элемент внутри списка, по element->$property === $value
     * @param string $property
     * @param string $value
     * @return $this|ContainsCollectionInterface
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws InvalidRegistrationOfPropertyException
     */
    public function fetchBy(string $property, $value): self
    {
        $list = new $this();
        if ($list instanceof ContainsCollectionInterface) {
            $method = static::getSerializer()->getGetterByCollection($list, $property);

            foreach ($this->elements as $key => $element) {
                if ($element->$method() === $value) {
                    $list->set($key, $element);
                }
            }
        }

        return $list;
    }

    /**
     * Ищет элемент внутри списка, по нахождению element->$property в массиве $values
     * @param string $property
     * @param array $values
     * @return $this|ContainsCollectionInterface
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws InvalidRegistrationOfPropertyException
     */
    public function fetchByArray(string $property, array $values): self
    {
        $list = new $this();
        if ($list instanceof ContainsCollectionInterface) {
            $method = static::getSerializer()->getGetterByCollection($list, $property);
            foreach ($this->elements as $key => $element) {
                if (in_array($element->$method(), $values)) {
                    $list->set($key, $element);
                }
            }
        }

        return $list;
    }

    /**
     * Переиндексирует list по свойству
     * @param string $property
     * @return $this|self|ContainsCollectionInterface
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws InvalidRegistrationOfPropertyException
     */
    public function reIndex(string $property): self
    {
        $list = new $this();
        if ($list instanceof ContainsCollectionInterface) {
            try {
                $method = static::getSerializer()->getGetterByCollection($list, $property);
            } catch (EntityIsNotChosenException $e) {
                $method = static::getSerializer()->getMethod($property);
            }
            foreach ($this->elements as $key => $element) {
                if (!method_exists($element, $method)) {
                    throw new EntityIsNotChosenException('Невозможно определить getter для переиндексации листа');
                }
                $list->set($element->$method(), $element);
            }
        }

        return $list;
    }

    /**
     * Отдает данные с одной колонки
     * @param string $property
     * @return array
     * @throws EntityIsNotChosenException
     * @throws EntityIsNotDescribedException
     * @throws InvalidRegistrationOfPropertyException
     */
    public function getColumn(string $property): array
    {
        $array = [];
        if ($this instanceof ContainsCollectionInterface) {
            $method = static::getSerializer()->getGetterByCollection($this, $property);

            $i = 0;
            foreach ($this->elements as $value) {
                $array[$value->$method()] = $i++;
            }
        }

        return array_flip($array);
    }
}