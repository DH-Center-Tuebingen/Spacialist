<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class PercentageAttribute extends AttributeBase
{
    protected static string $type = "percentage";
    protected static bool $inTable = false;
    protected static ?string $field = 'int_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        $data = IntegerAttribute::fromImport($data);
        
        if(intval($data) < 0 || intval($data) > 100)
            throw new InvalidDataException("Percentage must be between 0 and 100");
        
        return $data;
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
