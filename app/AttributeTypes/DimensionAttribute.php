<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class DimensionAttribute extends AttributeBase
{
    protected static string $type = "dimension";
    protected static bool $inTable = false;
    protected static ?string $field = 'json_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::guard($data);
        if($data === "") {
            return null;
        }
        
        $parts = explode(';', $data);

        if(count($parts) != 4) {
            throw new InvalidDataException("Given data does not match this datatype's format (VAL1;VAL2;VAL3;UNIT)");
        }

        return json_encode([
            'B' => floatval(trim($parts[0])),
            'H' => floatval(trim($parts[1])),
            'T' => floatval(trim($parts[2])),
            'unit' => trim($parts[3]),
        ]);
    }

    public static function unserialize(mixed $data) : mixed {
        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
