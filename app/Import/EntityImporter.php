<?php

namespace App\Import;

use App\File\Csv;
use App\Entity;
use App\Exceptions\AmbiguousValueException;
use App\Exceptions\ImportException;
use App\Exceptions\Structs\ImportExceptionStruct;
use App\Import\ImportResolution;

enum Action
{
    case CREATE;
    case UPDATE;
    case DELETE;
};

class EntityImporter
{

    private $metadata;

    private $attributesMap;
    private $entityTypeId;
    private $nameColumn;
    private $parentColumn = null;


    public function __construct($metadata, $data)
    {
        $this->metadata = $metadata;
        $this->nameColumn = trim($this->verifyDataKey('name_column', $data));
        $this->entityTypeId = trim($this->verifyDataKey('entity_type_id', $data));
        $this->attributesMap = $this->verifyDataKey('attributes', $data);

        // The parent column is optional, therefore we only set it 
        // to another value than null, if there is valid data set.
        if (array_key_exists('parent_column', $data)) {
            $parentColumn = trim($data['parent_column']);
            if (!empty($parentColumn)) {
                $this->parentColumn = trim($data['parent_column']);
            }
        }
    }

    private function verifyDataKey(string $key, array $data): mixed
    {
        if (!array_key_exists($key, $data)) {
            $err_data = new ImportExceptionStruct(on: $key);
            throw new ImportException("Data field '$key' not specified", $err_data);
        }
        return $data[$key];
    }

    public function validateImportData($handle)
    {
        $csvTable = new Csv($this->metadata['has_header_row'], $this->metadata['delimiter'], [], $this->metadata['encoding']);

        $result = [];
        $status = ImportResolution::getStatusArray();

        $csvTable->parseFile($handle, function ($row, $headers, $_, $index) use (&$result, &$status) {
            $info = $this->validateRowCallback($row, $index);
            $status[$info['status']]++;
            $result[$index] = $info;
        });
        $status['total'] = count($result);
        $status['items'] = $result;
        return $status;
    }

    private function validateRowCallback($row, $rowIndex)
    {
        $rootPath = $this->resolveRootPath($row, $rowIndex);
        $status = $this->checkIfEntityExists($rootPath);
        return [
            'path' => $rootPath,
            'status' => $status
        ];
    }

    private function resolveRootPath($row, $rowIndex)
    {
        $entityName = $row[$this->nameColumn];
        if (isset($this->parentColumn)) {
            $parent =  $row[$this->parentColumn];
            $parentEntity = Entity::getFromPath($parent);
            if (!isset($parentEntity)) {
                $exceptionData = new ImportExceptionStruct(
                    count: $rowIndex,
                    entry: $entityName,
                    on: $parent,
                    on_index: $this->parentColumn,
                    on_value: $parent
                );
                throw new ImportException("Parent entity does not exist at: '$parent'", $exceptionData);
            }
            $rootPath = $parent . "\\\\" . $entityName;
        }
        return $rootPath;
    }

    private function checkIfEntityExists($path)
    {
        try {
            $id = Entity::getFromPath($path);
            return $id == null ? ImportResolution::toName(ImportResolutionType::CREATE) : ImportResolution::toName(ImportResolutionType::UPDATE);
        } catch (AmbiguousValueException $e) {
            return ImportResolution::toName(ImportResolutionType::CONFLICT);
        }
    }
}
