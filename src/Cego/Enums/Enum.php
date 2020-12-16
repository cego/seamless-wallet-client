<?php


namespace Cego\Enums;


use ReflectionClass;
use Illuminate\Support\Collection;

abstract class Enum
{
    /**
     * Returns a collection with all constants
     *
     * @return Collection
     */
    public static function all(): Collection
    {
        $reflection = new ReflectionClass(static::class); // Static for late bindings

        return new Collection($reflection->getConstants());
    }
}