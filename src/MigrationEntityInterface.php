<?php

namespace Kluatr\Serializer;

/**
 * Interface MigrationEntityInterface
 * @package Kluatr\Serializer
 */
interface MigrationEntityInterface
{
    /**
     * [
     *  ClassName::class => [
     *      nativePropertyName => externalPropertyName
     *  ...
     * ]
     *  ClassName1::class => []
     * ...
     * ]
     * @return array
     */
    public function getEntityRelations():array ;
}