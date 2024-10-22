<?php

namespace App\Import;

use App\File\Csv;
use App\Entity;
use App\EntityType;
use App\EntityTypeRelation;
use App\Exceptions\AmbiguousValueException;
use App\Import\ImportResolution;
use App\Attribute;
use App\AttributeTypes\AttributeBase;
use App\ThConcept;
use Exception;

enum Action {
    case CREATE;
    case UPDATE;
    case DELETE;
};

class EntityImporter {

    const PARENT_DELIMITER = "\\\\";

    private $metadata;
    private array $attributesMap;
    private array $attributeAttributeMap = [];
    private int $entityTypeId;
    private string $nameColumn;
    private ?string $parentColumn = null;
    private ImportResolution $resolver;


    public function __construct($metadata, $data) {
        $this->resolver = new ImportResolution();
        $this->metadata = $metadata;

        $this->nameColumn = $data['name_column'] ?? '';
        $this->entityTypeId = $data['entity_type_id'];
        $this->attributesMap = $data['attributes'];

        // The parent column is optional, therefore we only set it 
        // to another value than null, if there is valid data set.
        if(array_key_exists('parent_column', $data)) {
            $parentColumn = trim($data['parent_column']);
            if(!empty($parentColumn)) {
                $this->parentColumn = trim($data['parent_column']);
            }
        }
    }

    private function validateStringData(string $varName, string $dataName) {
        if(gettype($this->{$varName}) == "string") {
            $this->{$varName} = trim($this->{$varName});
        }

        if(empty($this->{$varName})) {
            $this->resolver->conflict(__("entity-importer.missing-data", ["column" => $dataName]));
        }
    }

    private function validateAttributeData() {
        if(!is_array($this->attributesMap)) {
            $this->resolver->conflict(__("entity-importer.invalid-data", ["column" => "attributes", "value" => json_encode($this->attributesMap)]));
            return;
        }

        $this->attributesMap = array_map(function ($value) {
            return trim($value);
        }, $this->attributesMap);
    }

    public function validateImportData($filepath) {
        $this->validateStringData('nameColumn', 'name_column');
        $this->validateStringData('entityTypeId', 'entity_type_id');
        $this->validateAttributeData();

        if($this->resolver->hasErrors()) {
            return $this->resolver;
        }

        $handle = fopen($filepath, 'r');

        if(!$handle) {
            return $this->resolver->conflict(__("entity-importer.file-not-found", ["file" => $filepath]));
        }

        $csvTable = new Csv($this->metadata['has_header_row'], $this->metadata['delimiter'], $this->metadata['encoding']);

        try {
            $headers = $csvTable->parseHeaders($handle);
        } catch(\Exception $e) {
            return $this->resolver->conflict(__("entity-importer.empty"));
        }

        $this->verifyNameColumn($headers);
        $this->verifyParentColumn($headers);
        $this->verifyEntityType($this->entityTypeId);
        $this->verifyAttributeMapping($headers);

        if($this->resolver->hasErrors()) {
            return $this->resolver;
        }

        $csvTable->parse($handle, function ($row, $index) use (&$result, &$status) {
            $namesValid = $this->validateName($row, $index);
            if($namesValid) {
                // The location depends on the name column. If the name is not correct, we can't check the location.
                $this->validateLocation($row, $index);
            }
            $this->validateAttributesInRow($row, $index);
        });

        if($csvTable->getDataRows() == 0) {
            $this->resolver->conflict(__("entity-importer.empty"));
        }

        fclose($handle);
        return $this->resolver;
    }

    private function verifyNameColumn($headers): bool {
        if(empty($this->nameColumn) || !in_array($this->nameColumn, $headers)) {
            $this->resolver->conflict(__("entity-importer.name-column-does-not-exist", ["column" => $this->nameColumn]));
            return false;
        }
        return true;
    }

    private function verifyParentColumn($headers): bool {
        if(isset($this->parentColumn) && !in_array($this->parentColumn, $headers)) {
            $this->resolver->conflict(__("entity-importer.parent-column-does-not-exist", ["column" => $this->parentColumn]));
            return false;
        }
        return true;
    }

    private function verifyEntityType($entityTypeId): bool {
        if(!EntityType::find($entityTypeId)) {
            $this->resolver->conflict(__("entity-importer.entity-type-does-not-exist", ["entity_type_id" => $entityTypeId]));
            return false;
        }
        return true;
    }

    private function verifyAttributeMapping($headers): bool {
        $nameErrors = [];
        $indexErrors = [];
        foreach($this->attributesMap as $attribute => $column) {
            $column = trim($column);
            if($column == "") {
                continue;
            }

            if(!in_array($column, $headers)) {
                array_push($nameErrors, $column);
            }

            $attr = Attribute::find($attribute);
            if(!$attr) {
                array_push($indexErrors, $attribute);
            } else {
                $this->attributeAttributeMap[$column] = $attr;
            }
        }

        if(count($indexErrors) > 0) {
            $this->resolver->conflict(__("entity-importer.attribute-id-does-not-exist", ["attributes" => implode(", ", $indexErrors)]));
        }

        if(count($nameErrors) > 0) {
            $this->resolver->conflict(__("entity-importer.attribute-column-does-not-exist", ["columns" => implode(", ", $nameErrors)]));
        }

        if(count($indexErrors) > 0 || count($nameErrors) > 0) {
            return false;
        } else {
            return true;
        }
    }

    private function validateName($row, $rowIndex): bool {
        $entityName = $row[$this->nameColumn];

        if(gettype($entityName) == "string")
            $entityName = trim($entityName);

        if(empty($entityName)) {
            $this->rowConflict($rowIndex, "entity-importer.missing-name-in-row");
            return false;
        }

        return true;
    }

    private function validateLocation($row, $rowIndex): bool {

        $parentTypeId = null;
        $parentPath = $this->getParentColumn($row);
        if(!empty($parentPath)) {
            $parentId = Entity::getFromPath($parentPath);
            if($parentId == null) {
                $this->rowConflict($rowIndex, "entity-importer.parent-entity-does-not-exist", ["entity" => $parentPath]);
                return false;
            }

            $parent = Entity::find($parentId);
            $parentTypeId = $parent->entity_type_id;
        }

        $isAllowedAsChild = EntityTypeRelation::isAllowed($parentTypeId, $this->entityTypeId);
        if(!$isAllowedAsChild) {
            $childTh = EntityType::find($this->entityTypeId)->thesaurus_url;
            $childName = ThConcept::getLabel($childTh);

            $parentTh = EntityType::find($parentTypeId)->thesaurus_url;
            $parentName = ThConcept::getLabel($parentTh);

            $this->rowConflict($rowIndex, "entity-importer.entity-type-relation-not-allowed", ["child" => $childName, "parent" => $parentName]);
            return false;
        }

        $filepath = implode(self::PARENT_DELIMITER, array_filter([$parentPath, $row[$this->nameColumn]], fn ($part) => !empty($part)));
        if($this->checkIfEntityExists($filepath)) {
            $this->resolver->update();
        } else {
            $this->resolver->create();
        }

        return true;
    }

    private function validateAttributesInRow($row, $index): bool {
        $errors = [];
        foreach($this->attributeAttributeMap as $column => $attribute) {
            try {
                $datatype = $attribute->datatype;
                $attrClass = AttributeBase::getMatchingClass($datatype);
                $attrClass::fromImport($row[$column]);
            } catch(Exception $e) {
                array_push($errors, $column);
            }
        }

        if(count($errors) > 0) {
            $this->rowConflict($index, "entity-importer.attribute-could-not-be-imported", ["attribute" => implode(", ", $errors)]);
        }
        return count($errors) == 0;
    }

    private function rowConflict($rowIndex, $msg, $args = []) {
        $tmsg = __($msg, $args);
        $this->resolver->conflict("[" . ($rowIndex + 1) . "] " . $tmsg);
    }

    private function getParentColumn($row) {
        if(!isset($this->parentColumn)) {
            return null;
        }

        return trim($row[$this->parentColumn]);
    }


    private function checkIfParentDoesExist($row) {
        $parent = $this->getParentColumn($row);
        // When parent column is not set, or it is empty, it's a top level entity
        if($parent == null || $parent == "") {
            return true;
        }

        return $this->checkIfEntityExists($parent);
    }

    // private function resolveRootPath($row, $rowIndex) {
    //     $rootPath = "";
    //     $entityName = $row[$this->nameColumn];
    //     if(isset($this->parentColumn)) {
    //         $parent =  $row[$this->parentColumn];
    //         $parentEntity = Entity::getFromPath($parent);
    //         if(!isset($parentEntity)) {
    //             $exceptionData = new ImportExceptionStruct(
    //                 count: $rowIndex,
    //                 entry: $entityName,
    //                 on: $parent,
    //                 on_index: $this->parentColumn,
    //                 on_value: $parent
    //             );
    //             throw new ImportException("Parent entity does not exist at: '$parent'", 422, $exceptionData);
    //         }
    //         $rootPath = $parent . self::PARENT_DELIMITER . $entityName;
    //     }
    //     return $rootPath;
    // }

    private function checkIfEntityExists($path) {
        return $this->checkEntityResolution($path) != ImportResolutionType::CREATE;
    }

    private function checkEntityResolution($path): ImportResolutionType {
        try {
            $id = Entity::getFromPath($path);
            return $id == null ? ImportResolutionType::CREATE : ImportResolutionType::UPDATE;
        } catch(AmbiguousValueException $e) {
            return ImportResolutionType::CONFLICT;
        }
    }
}
