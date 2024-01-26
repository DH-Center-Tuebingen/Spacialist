<?php

namespace App\AttributeTypes;

class TimeperiodAttribute extends AttributeBase
{
    protected static $type = "timeperiod";
    protected static $inTable = true;
    protected static $field = 'json_val';

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
