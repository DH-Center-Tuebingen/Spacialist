<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\ThConcept;
use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class EpochAttribute extends AttributeBase
{
    protected static string $type = "epoch";
    protected static bool $inTable = false;
    protected static ?string $field = 'json_val';
    protected static bool $hasSelection = true;

    public static function getSelection(Attribute $a) {
        return ThConcept::getChildren($a->thesaurus_root_url, $a->recursive);
    }

    public static function fromImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::guard($data);
        if($data === "") {
            return null;
        }
        
        
        $startLabel = 'ad';
        $endLabel = 'ad';
        $parts = explode(';', $data);

        if(count($parts) != 3) {
            throw new InvalidDataException("Given data does not match this datatype's format (START;END;CONCEPT)");
        }

        $start = intval(trim($parts[0]));
        $end = intval(trim($parts[1]));

        if($end < $start) {
            throw new InvalidDataException("Start date must not be after end data ($start, $end)");
        }
        
        $concept = ThConcept::getByString(trim($parts[2]));
        $epoch = null;
        if(isset($concept)) {
            $epoch = [
                'id' => $concept->id,
                'concept_url' => $concept->concept_url,
            ];
        } else {
            throw new InvalidDataException("Given data is not a valid concept/label in the vocabulary");
        }
        if($start < 0) {
            $startLabel = 'bc';
            $start = abs($start);
        }
        if($end < 0) {
            $endLabel = 'bc';
            $end = abs($end);
        }
        return json_encode([
            'start' => $start,
            'startLabel' => $startLabel,
            'end' => $end,
            'endLabel' => $endLabel,
            'epoch' => $epoch,
        ]);
    }

    public static function unserialize(mixed $data) : mixed {
        $sl = isset($data['startLabel']) ? strtoupper($data['startLabel']) : null;
        $el = isset($data['endLabel']) ? strtoupper($data['endLabel']) : null;
        $s = $data['start'];
        $e = $data['end'];
        if(
            (isset($s) && !isset($sl))
            ||
            (isset($e) && !isset($el))
        ) {
            throw new InvalidDataException(__('You have to specify if your date is BC or AD.'));
        }
        if(
            ($sl == 'AD' && $el == 'BC')
            ||
            ($sl == 'BC' && $el == 'BC' && $s < $e)
            ||
            ($sl == 'AD' && $el == 'AD' && $s > $e)
        ) {
            throw new InvalidDataException(__('Start date of a time period must not be after it\'s end date'));
        }

        return json_encode($data);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
