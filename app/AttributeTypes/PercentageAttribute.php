<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class PercentageAttribute extends AttributeBase
{
    protected static string $type = "percentage";
    protected static bool $inTable = false;
    protected static ?string $field = 'dbl_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = DoubleAttribute::fromImport($data);
        if(DoubleAttribute::hasHigherPrecision($data, 2)) {
            throw InvalidDataException::requirePrecision($data);
        }
        if($data < 0 || $data > 100) {
            throw InvalidDataException::requireRange($data, 0, 100);
        }
        return floatval($data);;
    }

    public static function unserialize(mixed $data) : mixed {
        return floatval($data);
    }

    public static function serialize(mixed $data) : mixed {
        return floatval($data);
    }
}
