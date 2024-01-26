<?php

namespace App\AttributeTypes;

class IconclassAttribute extends AttributeBase
{
    protected static $type = "iconclass";
    protected static $inTable = true;
    protected static $field = 'int_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
