<?php

namespace App\AttributeTypes;

use App\Entity;
use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class EntityAttribute extends AttributeBase
{
    protected static string $type = "entity";
    protected static bool $inTable = true;
    protected static ?string $field = 'entity_val';
    protected static string $deleted_string = "error.deleted_entity";

    public static function parseImport(int|float|bool|string $data) : mixed {
        // TODO: This does not check if the entity that is selected is actually valid!
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $entityId = Entity::getFromPath($data);
        if($entityId === null) {
            throw InvalidDataException::invalidEntity($data);
        }

        return $entityId;
    }

    public static function unserialize(mixed $data) : mixed {
        // if($data["name"] == self::$deleted_string) {
        //     return null;
        // } else {
        //     return $data["id"];
        // }

        // Always return id prop, even if entity is deleted. Otherwise it wouldn't be visible in the frontend
        return $data["id"];
    }

    public static function serialize(mixed $data) : mixed {
        $entity = Entity::without(['user'])->find($data);
        return self::serializeFromEntity($data, $entity);
    }

    public static function serializeFromEntity(int $id, ?Entity $entity) : array {
       return [
            "id" => $id,
            "name" => isset($entity) ? $entity->name : self::$deleted_string,
        ];
    }
}
