<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class DoubleAttribute extends AttributeBase
{
    protected static string $type = "double";
    protected static bool $inTable = true;
    protected static ?string $field = 'dbl_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        if(is_string($data)){
            $data = trim($data);
            if(self::importDataIsMissing($data)) return null;
        }

        

        if(!is_numeric($data)) {
            throw new InvalidDataException("Given data is not a number");
        }
        return floatval($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
