<?php

namespace App\AttributeTypes;

class PercentageAttribute extends AttributeBase
{
    protected static $type = "percentage";
    protected static $inTable = false;
    protected static $field = 'int_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
