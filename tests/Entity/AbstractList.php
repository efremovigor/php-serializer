<?php

namespace Test\Kluatr\Serializer\Entity;

use Countable;
use Iterator;

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

    /**
     * @param array $elements
     */
    public function setElements(array $elements): void
    {
        $this->elements = $elements;
    }
}