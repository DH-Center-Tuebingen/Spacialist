<?php

namespace App\Import;

use App\File\Csv;
use App\Entity;
use App\Exceptions\AmbiguousValueException;
use App\Import\ImportResolution;

enum Action {
    case CREATE;
    case UPDATE;
    case DELETE;
};



class EntityImporter {

    private $metadata;
    private $data;


    public function __construct($metadata, $data) {
        $this->metadata = $metadata;
        $this->data = $data;
    }


    public function validateImportData($handle) {
        $csvTable = new Csv($this->metadata['has_header_row'], $this->metadata['delimiter'], [], $this->metadata['encoding']);

        $result = [];
        $status = ImportResolution::getStatusArray();

        $csvTable->parseFile($handle, function ($row, $headers, $_, $index) use (&$result, &$status) {
            $info = $this->validateRowCallback($row, $this->data);
            $status[$info['status']]++;
            $result[$index] = $info;
        });
        $status['total'] = count($result);
        $status['items'] = $result;
        return $status;
    }

    private function validateRowCallback($row, $data) {
        $name = $row[$data['name_column']];

        if (array_key_exists('parent_column', $data)) {
            $parent =  $row[$data['parent_column']];
            $rootPath = $parent . "\\\\" . $name;
        } else {
            $rootPath = $name;
        }

        $status = $this->checkIfEntityExists($rootPath);
        return [
            'path' => $rootPath,
            'status' => $status
        ];
    }

    private function checkIfEntityExists($path) {
        try {
            $id = Entity::getFromPath($path);
            return $id == null ? ImportResolution::toName(ImportResolutionType::CREATE) : ImportResolution::toName(ImportResolutionType::UPDATE);
        } catch (AmbiguousValueException $e) {
            return ImportResolution::toName(ImportResolutionType::CONFLICT);
        }
    }
}
