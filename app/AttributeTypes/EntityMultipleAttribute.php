<?php

namespace App\AttributeTypes;

use App\Entity;

class EntityMultipleAttribute extends AttributeBase
{
    protected static string $type = "entity-mc";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        $idList = [];
        $parts = explode(';', $data);
        foreach($parts as $part) {
            $trimmedPart = trim($part);
            $idList[] = Entity::getFromPath($trimmedPart);
        }
        return json_encode($idList);
    }

    public static function unserialize(mixed $data) : mixed {
        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
