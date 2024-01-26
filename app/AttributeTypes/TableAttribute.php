<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\ThConcept;

class TableAttribute extends AttributeBase
{
    protected static $type = "table";
    protected static $inTable = false;
    protected static $field = 'json_val';
    protected static $hasSelection = true;

    public static function getSelection(Attribute $a) {
        // string-sc is the only allowed type with selections in tables
        // TODO replace with all types matching $inTable = true ans $hasSelection = true
        $columns = Attribute::where('parent_id', $a->id)
            ->where('datatype', 'string-sc')
            ->get();
        $selection = [];
        foreach($columns as $c) {
            $selection[$c->id] = ThConcept::getChildren($c->thesaurus_root_url, $c->recursive);
        }
        return $selection;
    }

    public function unserialize(string $data) : mixed {
        info("Should unserialize $data!");
    }

    public function serialize(mixed $data) : mixed {
        info("Should serialize data!");
    }
}
