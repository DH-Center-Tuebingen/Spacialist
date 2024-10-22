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

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $parts = array_map(fn($str) => trim($str), explode(';', $data));

        if(count($parts) != 3) {
            throw InvalidDataException::requiredFormat("START;END;CONCEPT", $data);
        }

        $timeperiod = json_decode(TimeperiodAttribute::parseImport($parts[0] . ';' . $parts[1]), true);
        $conceptString = trim($parts[2]);

        $epoch = null;
        if($conceptString !== '') {
            // TODO: This does not check if the entity that is selected is actually valid!
            $concept = ThConcept::getByString(trim($parts[2]));

            if(isset($concept)) {
                // Discuss: Do we need the concept_url here? Otherwise we could just use the EntityAttribute::parseImport
                $epoch = [
                    'id' => $concept->id,
                    'concept_url' => $concept->concept_url,
                ];
            } else {
                throw InvalidDataException::invalidConcept($conceptString);
            }
        }

        return json_encode(
            array_merge($timeperiod,[
                'epoch' => $epoch,
            ])
        );
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