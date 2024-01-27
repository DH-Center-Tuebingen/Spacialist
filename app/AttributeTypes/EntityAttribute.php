<?php

namespace App\AttributeTypes;

use App\Entity;

class EntityAttribute extends AttributeBase
{
    protected static string $type = "entity";
    protected static bool $inTable = true;
    protected static ?string $field = 'entity_val';

    public static function fromImport(string $data) : mixed {
        return Entity::getFromPath($data);
    }

    public static function unserialize(mixed $data) : mixed {
        return $data;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
