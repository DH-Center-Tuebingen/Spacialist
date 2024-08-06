<?php

namespace App\AttributeTypes;

use App\Entity;

class EntityAttribute extends AttributeBase
{
    protected static string $type = "entity";
    protected static bool $inTable = true;
    protected static ?string $field = 'entity_val';
    protected static string $deleted_string = "error.deleted_entity";

    public static function fromImport(int|float|bool|string $data) : mixed {
        return Entity::getFromPath($data);
    }

    public static function unserialize(mixed $data) : mixed {
        if($data["name"] == self::$deleted_string) {
            return null;
        } else {
            return $data["id"];
        }
    }

    public static function serialize(mixed $id) : mixed {
        $entity = Entity::without(['user'])->find($id);
        return self::serializeFromEntity($id, $entity);
    }
    
    public static function serializeFromEntity($id, $entity){
       return [
            "id" => $id,
            "name" => isset($entity) ? $entity->name : self::$deleted_string,
        ];
    }
}
