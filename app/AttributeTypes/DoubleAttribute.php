<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class DoubleAttribute extends AttributeBase
{
    protected static string $type = "double";
    protected static bool $inTable = true;
    protected static ?string $field = 'dbl_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        if(!is_numeric($data)) {
            throw InvalidDataException::requireNumeric($data);
        }
        return floatval($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }

    public static function hasHigherPrecision(float $value, int $maxPrecision = 2): bool {
        if($maxPrecision < 0) {
            return false;
        }

        $precision = pow(10, $maxPrecision);
        return (string)($value * $precision) !== (string)(int)($value * $precision);
    }
}
