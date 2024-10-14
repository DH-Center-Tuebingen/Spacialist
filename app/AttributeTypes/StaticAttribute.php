<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

abstract class StaticAttribute extends AttributeBase
{
    public static final function parseImport(int|float|bool|string $data) : mixed {
        $className = static::class;
        throw new InvalidDataException("$className does not support import.");
    }   
}