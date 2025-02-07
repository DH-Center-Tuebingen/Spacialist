<?php

namespace App\AttributeTypes;

use Illuminate\Support\Facades\DB;

class SqlAttribute extends StaticAttribute
{
    protected static string $type = "sql";
    protected static bool $inTable = false;
    protected static ?string $field = null;

    public static function unserialize(mixed $data) : mixed {
        return false;
    }

    public static function serialize(mixed $data) : mixed {
        return false;
    }

    public static function execute(string $sql, int $entityId) : array|string {
        // if entity_id is referenced several times
        // add an incrementing counter, so the
        // references are unique (required by PDO)
        $i = 0;
        $safes = [];
        $text = preg_replace_callback('/:entity_id/', function($matches) use (&$i, &$safes, $entityId) {
            ++$i;
            $safes[':entity_id_' . $i] = $entityId;
            return $matches[0] . '_' . $i;
        }, $sql);

        DB::beginTransaction();
        $sqlValue = DB::select($text, $safes);
        DB::rollBack();

        // Check if only one result exists
        if(count($sqlValue) === 1) {
            // Get all column indices (keys) using the first row
            $valueKeys = array_keys(get_object_vars($sqlValue[0]));
            // Check if also only one key/column exists
            if(count($valueKeys) === 1) {
                // If only one row and one column exist,
                // return plain value instead of array
                $firstKey = $valueKeys[0];
                $sqlValue = $sqlValue[0]->{$firstKey};
            }
        }
        return $sqlValue;
    }
}
