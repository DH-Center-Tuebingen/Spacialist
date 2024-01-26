<?php

namespace App\AttributeTypes;

class ListAttribute extends AttributeBase
{
    protected static $type = "list";
    protected static $inTable = false;
    protected static $field = 'json_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
