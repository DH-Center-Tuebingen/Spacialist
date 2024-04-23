<?php

namespace App\Import;


enum ImportResolutionType {
    case CREATE;
    case UPDATE;
    case CONFLICT;
}

class ImportResolution {

    static function toName(ImportResolutionType $state) {
        switch ($state) {
            case ImportResolutionType::CREATE:
                return "create";
            case ImportResolutionType::UPDATE:
                return "update";
            case ImportResolutionType::CONFLICT:
                return "conflict";
            default:
                return "unknown";
        }
    }

    static function getStatusArray() {
        return [
            self::toName(ImportResolutionType::CREATE) => 0,
            self::toName(ImportResolutionType::UPDATE) => 0,
            self::toName(ImportResolutionType::CONFLICT) => 0,
        ];
    }
}
