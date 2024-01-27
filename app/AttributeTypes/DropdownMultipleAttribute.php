<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\ThConcept;

class DropdownMultipleAttribute extends AttributeBase
{
    protected static string $type = "string-mc";
    protected static bool $inTable = false;
    protected static ?string $field = 'json_val';
    protected static bool $hasSelection = true;

    public static function getSelection(Attribute $a) {
        return ThConcept::getChildren($a->thesaurus_root_url, $a->recursive);
    }

    public static function fromImport(string $data) : mixed {
        $convValues = [];
        $parts = explode(';', $data);
        foreach($parts as $part) {
            $trimmedPart = trim($part);
            $concept = ThConcept::getByString($trimmedPart);
            if(isset($concept)) {
                $convValues[] = [
                    'id' => $concept->id,
                    'concept_url' => $concept->concept_url,
                ];
            } else {
                throw new InvalidDataException("Given data part ($trimmedPart) is not a valid concept/label in the vocabulary");
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
