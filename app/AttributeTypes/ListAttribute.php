<?php

namespace App\AttributeTypes;

use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class ListAttribute extends AttributeBase
{
    protected static string $type = "list";
    protected static bool $inTable = false;
    protected static ?string $field = 'json_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $trimmedValues = [];
        $parts = explode(';', $data);
        foreach($parts as $part) {
            $trimmedValues[] = trim($part);
        }
        return json_encode($trimmedValues);
    }

    public static function parseExport(mixed $data) : string {
        return implode(";", json_decode($data));
    }

    public static function unserialize(mixed $data) : mixed {
        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
