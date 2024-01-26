<?php

namespace App\AttributeTypes;

class DoubleAttribute extends AttributeBase
{
    protected static $type = "double";
    protected static $inTable = true;
    protected static $field = 'dbl_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
