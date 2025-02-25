<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\NumberUtils;
use App\Utils\StringUtils;

class RismAttribute extends AttributeBase
{
    protected static string $type = "rism";
    protected static bool $inTable = true;
    protected static ?string $field = 'str_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        if($data == "") {
            return null;
        }

        if(!NumberUtils::is_unsigned_integer_string($data)) {
            throw InvalidDataException::invalidDefinition("RISM", $data);
        }

        return $data;
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
