<?php

namespace App\AttributeTypes;

use App\Entity;
use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class EntityMultipleAttribute extends AttributeBase
{
    protected static string $type = "entity-mc";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $missingEntitiesList = [];
        $idList = [];
        $parts = explode(';', $data);
        foreach($parts as $part) {
            $trimmedPart = trim($part);
            $entityId = Entity::getFromPath($trimmedPart);
            if($entityId === null) {
                $missingEntitiesList[] = $trimmedPart;
            } else {
                $idList[] = $entityId;
            }
        }

        if(count($missingEntitiesList) > 0) {
            throw InvalidDataException::invalidEntities($missingEntitiesList);
        }

        return json_encode($idList);
    }

    public static function parseExport(mixed $data) : string {
        return implode(';', array_map(function($id) {
            return Entity::find($id)->name;
        }, json_decode($data)));
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
