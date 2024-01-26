<?php

namespace App\AttributeTypes;

class EntityMultipleAttribute extends AttributeBase
{
    protected static $type = "entity-mc";
    protected static $inTable = true;
    protected static $field = 'json_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
