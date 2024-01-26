<?php

namespace App\AttributeTypes;

class RismAttribute extends AttributeBase
{
    protected static $type = "rism";
    protected static $inTable = true;
    protected static $field = 'str_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
