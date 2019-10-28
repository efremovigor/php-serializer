<?php

namespace Kluatr\Serializer;

/**
 * Interface MigrationEntityInterface
 * @package Kluatr\Serializer
 */
interface MigrationEntityInterface
{
    /**
     * @return array
     * [
     *  ClassName::class => [
     *      nativePropertyName => externalPropertyName
     *      ...
     *  ]
     *  ClassName1::class => []
     *  ...
     *  ]
     */
    public function getEntityRelations(): array;
}