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
            $id = EntityAttribute::unserialize($entry);
            if($id !== null) {
                $result[] =  $id;
            }
        }
        return json_encode($result);
    }

    public static function serialize(mixed $ids) : mixed {
        $decodedData = json_decode($ids);
        $entityList = Entity::without(['user'])->whereIn('id', $decodedData)->get()->keyBy('id');
        return array_map(function($id) use ($entityList) {
            return EntityAttribute::serializeFromEntity($id, $entityList[$id]);
        }, $decodedData);
    }
}
