<?php

namespace Kluatr\Serializer;

use Kluatr\Serializer\Error\EntityIsNotChosenException;

/**
 * Поведение класса содержащий коллекцию классов одного типа
 *
 * Interface ContainsCollectionInterface
 * @package Helpers
 */
interface ContainsCollectionInterface
{
    /**
     * Имя класса списка
     * @return string
     * @throws EntityIsNotChosenException
     */
    public function getClass(): string;

    /**
     * Сохранение элемента списка
     * @param $key
     * @param $element
     */
    public function set($key, $element): void;

    /**
     * Получение элементов списка
     * @return array
     */
    public function getElements(): array;

    /**
     * Назначение элементов списка
     * @param array $elements
     */
    public function setElements(array $elements): void;

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key);

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool;
}