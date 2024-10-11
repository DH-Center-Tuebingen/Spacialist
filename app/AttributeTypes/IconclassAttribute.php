<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class IconclassAttribute extends AttributeBase
{
    protected static string $type = "iconclass";
    protected static bool $inTable = true;
    protected static ?string $field = 'str_val';

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
