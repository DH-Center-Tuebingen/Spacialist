<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\ThConcept;

class TableAttribute extends AttributeBase
{
    protected static string $type = "table";
    protected static bool $inTable = false;
    protected static ?string $field = 'json_val';
    protected static bool $hasSelection = true;

    public static function getSelection(Attribute $a) {
        $types = array_map(function(array $entry) {
            return $entry["datatype"];
        }, AttributeBase::getTypes(['in_table' => true, 'has_selection' => true]));

        $columns = Attribute::where('parent_id', $a->id)
            ->whereIn('datatype', $types)
            ->get();
        $selection = [];
        foreach($columns as $c) {
            $selection[$c->id] = $c->getSelection();
        }
        return $selection;
    }

    public static function fromImport(string $data) : mixed {
        return null;
    }

    public static function unserialize(mixed $data) : mixed {
        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
