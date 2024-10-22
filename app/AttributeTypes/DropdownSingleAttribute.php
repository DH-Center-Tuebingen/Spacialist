<?php

namespace App\AttributeTypes;

use App\Attribute;
use App\ThConcept;
use App\Exceptions\InvalidDataException;
use App\Utils\StringUtils;

class DropdownSingleAttribute extends AttributeBase
{
    protected static string $type = "string-sc";
    protected static bool $inTable = true;
    protected static ?string $field = 'thesaurus_val';
    protected static bool $hasSelection = true;

    public static function getSelection(Attribute $a) {
        return ThConcept::getChildren($a->thesaurus_root_url, $a->recursive);
    }

    public static function parseImport(int|float|bool|string $data) : mixed {
        $data = StringUtils::useGuard(InvalidDataException::class)($data);  
        $concept = ThConcept::getByString($data);
        if(isset($concept)) {
            return $concept->concept_url;
        } else {
            throw InvalidDataException::invalidConcept($data);
        }
    }

    public static function unserialize(mixed $data) : mixed {
        return $data['concept_url'];
    }

    public static function serialize(mixed $data) : mixed {
        return $data;
    }
}
