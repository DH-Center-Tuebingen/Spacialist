<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Model\Attributes\AttributeValue\TypedValues\IntegerAttributeValue;
use App\Utils\NumberUtils;

class SuperiorIntegerAttribute extends SuperiorAttributeBase
{
    protected static string $type = "superior-integer";
    protected static bool $inTable = true;
    protected static ?string $field = 'value';
    protected static string $modelClass = IntegerAttributeValue::class;

    public static function parseImport(int|float|bool|string $data): mixed
    {
        return NumberUtils::useStringIntegerGuard(InvalidDataException::class)($data);
    }

    public static function unserialize(mixed $data): mixed
    {
        return $data;
    }

    public static function serialize(mixed $data): mixed
    {
        if(is_int($data)) {
            return $data;
        } else if(is_string($data)) {
            $data = trim($data);
            if(!NumberUtils::is_integer_string($data)) {
                throw new InvalidDataException("Given data is not an integer");
            }
            return intval($data);
        } else {
            throw new InvalidDataException("Given data is not an integer");
        }
    }

    public static function parseExport(mixed $data): string
    {
        return (string)$data;
    }
}
