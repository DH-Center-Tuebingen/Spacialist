<?php

namespace App\AttributeTypes;

class DateAttribute extends AttributeBase
{
    protected static $type = "date";
    protected static $inTable = true;
    protected static $field = 'date_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
