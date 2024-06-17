<?php

namespace App\Exceptions\Structs;

use JsonSerializable;

class ImportExceptionStruct implements JsonSerializable {
    public $count;
    public $entry;
    public $on;
    public $on_index;
    public $on_value;
    public $on_name;

    public function __construct($count = -1, $entry = null, $on = -1, $on_index = -1, $on_value = null, $on_name = null) {
        $this->count = $count;
        $this->entry = $entry;
        $this->on = $on;
        $this->on_index = $on_index;
        $this->on_value = $on_value;
        $this->on_name = $on_name;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }

    public function __toString() {
        return json_encode($this);
    }
}
