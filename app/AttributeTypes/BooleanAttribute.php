<?php

namespace App\AttributeTypes;

class BooleanAttribute extends AttributeBase {
    protected static string $type = "boolean";
    protected static bool $inTable = true;
    protected static ?string $field = 'int_val';

    public static function fromImport(int|float|bool|string $data): mixed {

        if (is_string($data)) {
            $data = strtolower(trim($data));
        }

        return
            $data == 1 || $data == '1' || $data == 'x' ||
            $data == 'true' || $data == 't' ||
            intval($data) > 0;
    }

    public static function unserialize(mixed $data): mixed {
        return $data;
    }

    public static function serialize(mixed $data): mixed {
        return $data;
    }
}
