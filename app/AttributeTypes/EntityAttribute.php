<?php

namespace App\AttributeTypes;

class EntityAttribute extends AttributeBase
{
    protected static $type = "entity";
    protected static $inTable = true;
    protected static $field = 'entity_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
