<?php

namespace App\Exceptions\Structs;

use JsonSerializable;

class AttributeImportExceptionStruct implements JsonSerializable {

    public $type;
    public $columnIndex;
    public $columnName;

    public function __construct($type = -1, $columnIndex = -1, $columnName = null) {
        $this->type = $type;
        $this->columnIndex = $columnIndex;
        $this->columnName = $columnName;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }

    public function __toString() {
        return json_encode($this);
    }
}
