<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\Exceptions\InvalidDataException;
use App\ThConcept;
use App\Utils\StringUtils;

class DropdownMultipleAttribute extends AttributeBase
{
    protected static string $type = "string-mc";
    protected static bool $inTable = true;
    protected static ?string $field = 'json_val';
    protected static bool $hasSelection = true;

    public static function getSelection(Attribute $a) {
        return ThConcept::getChildren($a->thesaurus_root_url, $a->recursive);
    }

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);
        $convValues = [];
        $parts = explode(';', $data);
        foreach($parts as $part) {
            $trimmedPart = trim($part);
            $concept = ThConcept::getByString($trimmedPart); // Discuss: Problematic when there is another concept with the same name AND also when the concept is NOT available in this dropdown.
            if(isset($concept)) {
                $convValues[] = [
                    'id' => $concept->id,
                    'concept_url' => $concept->concept_url,
                ];
            } else {
                throw InvalidDataException::invalidConcept($trimmedPart);
            }
        }
        return json_encode($convValues);
    }

    public static function unserialize(mixed $data) : mixed {
        $thesaurus_urls = array_map(function($entry) {
            return [
                "concept_url" => $entry['concept_url'],
                "id" => $entry['id'],
            ];
        }, $data);
        return json_encode($thesaurus_urls);
    }

    public static function serialize(mixed $data) : mixed {
        return json_decode($data);
    }
}
