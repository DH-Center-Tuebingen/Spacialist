<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\ThConcept;

class EpochAttribute extends AttributeBase
{
    protected static $type = "epoch";
    protected static $inTable = false;
    protected static $field = 'json_val';
    protected static $hasSelection = true;

    public static function getSelection(Attribute $a) {
        return ThConcept::getChildren($a->thesaurus_root_url, $a->recursive);
    }

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
