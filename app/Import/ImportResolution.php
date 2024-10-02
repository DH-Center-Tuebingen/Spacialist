<?php

namespace App\Import;


enum ImportResolutionType {
    case CREATE;
    case UPDATE;
    case CONFLICT;
}

class ImportResolution {

    private array $status;
    private array $errors = [];

    public function __construct() {
        $this->clear();
    }

    public function clear() {
        $this->status = [
            ImportResolutionType::CREATE->name => 0,
            ImportResolutionType::UPDATE->name => 0,
            ImportResolutionType::CONFLICT->name => 0,
        ];
        $this->errors = [];
    }

    public function create(): ImportResolution {
        $this->status[ImportResolutionType::CREATE->name]++;
        return $this;
    }

    public function update(): ImportResolution {
        $this->status[ImportResolutionType::UPDATE->name]++;
        return $this;
    }

    public function conflict(string $message): ImportResolution {
        $this->errors[] = $message;
        $this->status[ImportResolutionType::CONFLICT->name]++;
        info($message);
        return $this;
    }

    public function getSummary() {
        return [
            self::getKey(ImportResolutionType::CREATE) => $this->status[ImportResolutionType::CREATE->name],
            self::getKey(ImportResolutionType::UPDATE) => $this->status[ImportResolutionType::UPDATE->name],
            self::getKey(ImportResolutionType::CONFLICT) => $this->status[ImportResolutionType::CONFLICT->name],
        ];
    }

    public static function getKey(ImportResolutionType $type) {
        return strtolower($type->name);
    }

    public function hasErrors() {
        return count($this->errors) > 0;
    }

    public function getErrors() {
        return $this->errors;
    }
}
