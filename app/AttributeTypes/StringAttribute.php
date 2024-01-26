<?php

namespace App\AttributeTypes;

class StringAttribute extends AttributeBase
{
    protected static $type = "string";
    protected static $inTable = true;
    protected static $field = 'str_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
