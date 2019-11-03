<?php


namespace Kluatr\Serializer;

/**
 * Interface SerializerInterface
 * @package Kluatr\Serializer
 */
interface SerializerInterface
{
    public function normalize($source, $subject = null, int $flags);

    public function serialize($source, string $type, int $flags);

    public function jsonSignificant($source, int $flags);

    public function entityFill($data, $entity);

    public function isJson($data);
}