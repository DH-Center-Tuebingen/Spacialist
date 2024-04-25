<?php

namespace App\Import;

use JsonSerializable;

class ImportExceptionData implements JsonSerializable {
    public $count;
    public $entry;
    public $on;
    public $on_index;
    public $on_value;

    public function __construct($count = -1, $entry = null, $on = -1, $on_index = -1, $on_value = null) {
        $this->count = $count;
        $this->entry = $entry;
        $this->on = $on;
        $this->on_index = $on_index;
        $this->on_value = $on_value;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }

    public function __toString() {
        return json_encode($this);
    }
}
