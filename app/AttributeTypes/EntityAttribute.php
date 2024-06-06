<?php

namespace App\AttributeTypes;

use App\Entity;

class EntityAttribute extends AttributeBase
{
    protected static string $type = "entity";
    protected static bool $inTable = true;
    protected static ?string $field = 'entity_val';

    public static function fromImport(int|float|bool|string $data) : mixed {
        return Entity::getFromPath($data);
    }

    public static function unserialize(mixed $data) : mixed {
        if($data["name"] == "error.deleted_entity") {
            return null;
        } else {
            return $data["id"];
        }
    }

    public static function serialize(mixed $data) : mixed {
        $entity = Entity::without(['user'])->find($data);
        return [
            "id" => $data,
            "name" => isset($entity) ? $entity->name : "error.deleted_entity",
        ];
    }
}
