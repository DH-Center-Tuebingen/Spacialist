<?php

namespace App\AttributeTypes;

class SerialAttribute extends AttributeBase
{
    protected static $type = "serial";
    protected static $inTable = false;
    protected static $field = null;

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
