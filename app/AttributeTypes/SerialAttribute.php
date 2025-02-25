<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\AttributeValue;
use App\Entity;
use App\EntityType;
use App\User;

class SerialAttribute extends StaticAttribute
{
    protected static string $type = "serial";
    protected static bool $inTable = false;
    protected static ?string $field = "str_val";

    private static function create(int $eid, int $aid, string $text, int $ctr, int $uid) {
        $av = new AttributeValue();
        $av->entity_id = $eid;
        $av->attribute_id = $aid;
        $av->str_val = sprintf($text, $ctr);
        $av->user_id = $uid;
        $av->save();
    }

    public static function unserialize(mixed $data) : mixed {
        return false;
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }

    public static function handleOnCreate(Entity $entity, Attribute $attr, User $user) : void {
        $nextValue = 1;
        $cleanedRegex = preg_replace('/(.*)(%\d*d)(.*)/i', '/$1(\d+)$3/i', $attr->text);

        // get last added
        $lastEntity = Entity::where('entity_type_id', $entity->entity_type_id)
            ->orderBy('created_at', 'desc')
            ->skip(1)
            ->first();
        if(isset($lastEntity)) {
            $lastValue = AttributeValue::where('attribute_id', $attr->id)
                ->where('entity_id', $lastEntity->id)
                ->first();
            if(isset($lastValue)) {
                $nextValue = intval(preg_replace($cleanedRegex, '$1', $lastValue->str_val));
                $nextValue++;
            }
        }

        self::create($entity->id, $attr->id, $attr->text, $nextValue, $user->id);
    }

    public static function handleOnAdd(Attribute $attr, EntityType $entityType, User $user) : void {
        // add attribute to all existing entities
        $entites = Entity::where('entity_type_id', $entityType->id)
            ->orderBy('created_at', 'asc')
            ->get();
        $ctr = 1;
        foreach($entites as $e) {
            self::create($e->id, $attr->id, $attr->text, $ctr, $user->id);
            $ctr++;
        }
    }
}
