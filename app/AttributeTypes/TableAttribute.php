<?php

namespace App\AttributeTypes;

use App\Attribute;

class TableAttribute extends AttributeBase
{
    protected static string $type = "table";
    protected static bool $inTable = false;
    protected static ?string $field = 'json_val';
    protected static bool $hasSelection = true;

    public static function getSelection(Attribute $a) {
        $types = array_map(function(array $entry) {
            return $entry["datatype"];
        }, AttributeBase::getTypes(true, ['in_table' => true, 'has_selection' => true]));

        $columns = Attribute::where('parent_id', $a->id)
            ->whereIn('datatype', $types)
            ->get();
        $selection = [];
        foreach($columns as $c) {
            $selection[$c->id] = $c->getSelection();
        }
        return $selection;
    }

    public static function parseImport(int|float|bool|string $data) : mixed {
        return null;
    }

    public static function parseExport(mixed $data) : string {
        return '';
    }

    public static function unserialize(mixed $data) : mixed {
        $attributeTypes = Attribute::whereNotNull('parent_id')
            ->whereIn('datatype', ['entity', 'entity-mc'])
            ->pluck('datatype', 'id')
            ->toArray();
        foreach($data as $rowId => $row) {
            // skip empty rows
            if(count($row) === 0) continue;
            foreach($row as $aid => $colValue) {
                foreach($attributeTypes as $tid => $type) {
                    if($aid == $tid) {
                        if($type == 'entity') {
                            $data[$rowId][$aid] = EntityAttribute::unserialize($colValue);
                        } else if($type == 'entity-mc') {
                            $data[$rowId][$aid] = EntityMultipleAttribute::unserialize($colValue);
                        }
                    }
                }
            }
        }
        // array_filter with one argument (no callback) filters all empty (`empty()`) values
        $cleanData = array_values(array_filter($data));
        return json_encode($cleanData);
    }

    public static function serialize(mixed $data) : mixed {
        $attributeTypes = Attribute::whereNotNull('parent_id')
            ->whereIn('datatype', ['entity', 'entity-mc'])
            ->select('id', 'datatype')
            ->get();
        $decodedData = json_decode($data);
        foreach($decodedData as $rowId => $row) {
            foreach($row as $aid => $colValue) {
                foreach($attributeTypes as $type) {
                    if($aid == $type["id"]) {
                        if($type["datatype"] == 'entity') {
                            $decodedData[$rowId]->{$aid} = EntityAttribute::serialize($colValue);
                        } else if($type["datatype"] == 'entity-mc') {
                            $decodedData[$rowId]->{$aid} = EntityMultipleAttribute::serialize($colValue);
                        }
                    }
                }
            }
        }
        return $decodedData;
    }
}
