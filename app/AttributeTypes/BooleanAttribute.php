<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;

class BooleanAttribute extends AttributeBase {
    protected static string $type = "boolean";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $boolean = false;

        if(is_bool($data)) {
            $boolean = $data;
        } else if(is_numeric($data)) {
            $boolean = floatval($data) > 0;
        } else if(is_string($data)) {
            $truthy = ['true', 't', 'x', 'wahr', 'w'];
            $string_val = strtolower(trim($data));
            $boolean = in_array($string_val, $truthy, true);
        } else {
            throw InvalidDataException::requireBoolean($data);
        }

        return $boolean;
    }

    public static function unserialize(mixed $data): mixed {
        return $data;
    }

    public static function serialize(mixed $data): mixed {
        return $data;
    }
}
