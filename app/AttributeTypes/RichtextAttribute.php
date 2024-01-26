<?php

namespace App\AttributeTypes;

class RichtextAttribute extends AttributeBase
{
    protected static $type = "richtext";
    protected static $inTable = false;
    protected static $field = 'str_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
