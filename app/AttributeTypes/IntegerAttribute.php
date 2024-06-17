<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class IntegerAttribute extends AttributeBase
{
    protected static string $type = "integer";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        if(!is_int($data) && !ctype_digit($data)) {
            throw new InvalidDataException("Given data is not an integer");
        }
        return intval($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
