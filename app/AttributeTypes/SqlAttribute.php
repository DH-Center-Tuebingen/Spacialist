<?php

namespace App\AttributeTypes;

class SqlAttribute extends AttributeBase
{
    protected static string $type = "sql";
    protected static bool $inTable = false;
    protected static ?string $field = null;

    public static function parseImport(int|float|bool|string $data) : mixed {
        return null;
    }

    public static function unserialize(mixed $data) : mixed {
        return false;
    }

    public static function serialize(mixed $data) : mixed {
        return false;
    }
}
