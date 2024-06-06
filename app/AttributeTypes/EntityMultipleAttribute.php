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
        $result = [];
        foreach($data as $entry) {
            if($entry["name"] == "error.deleted_entity") {
                continue;
            }

            $result[] = $entry["id"];
        }
        return json_encode($result);
    }

    public static function serialize(mixed $data) : mixed {
        $decodedData = json_decode($data);
        $entityList = Entity::without(['user'])->whereIn('id', $decodedData)->pluck('name', 'id');
        return array_map(function($value) use ($entityList) {
            return [
                "id" => $value,
                "name" => $entityList[$value] ?? "error.deleted_entity",
            ];
        }, $decodedData);
    }
}
