<?php


namespace Kluatr\Serializer;

/**
 * Interface SerializerInterface
 * @package Kluatr\Serializer
 */
interface SerializerInterface
{
    public function normalize($source, $subject = null, int $flags = 0);

    public function serialize(string $type = 'json', int $flags = 0);

    public function jsonSignificant($source, int $flags = 0);

    public function entityFill($data, $entity);

    public function isJson($data);
}