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
