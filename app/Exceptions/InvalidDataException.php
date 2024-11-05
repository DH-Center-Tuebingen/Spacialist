<?php

namespace App\Exceptions;

use Exception;

class InvalidDataException extends Exception
{
    function __construct(string $message, string $actual = null) {
        if($actual !== null) {
            $pattern = '/\s*\.\s*$/';
            // Use preg_replace to remove the last dot in the string
            $result = preg_replace($pattern, '', $message);
            $message = $result . '  -  ' . $actual;
        }

        parent::__construct($message);
    }

    private static function attributeString() {
        return __('dictionary.value');
    }

    public static function requiredFormat(string $requiredFormat, string $actual) {
        return new self(__('validation.import_format', ['format' => $requiredFormat]), $actual);
    }

    public static function requireBoolean(string $actual) {
        return new self(__('validation.boolean'), $actual);
    }

    public static function requireTypes(string $type, array $actualValues) {
        return new self(__('validation.types', ['type' => $type]), implode(', ', $actualValues));
    }

    public static function requirePositiveInteger(string $actual) {
        return new self(__('validation.integer_positive', ['attribute' => self::attributeString()], $actual));
    }

    public static function requireNumeric(string $actual) {
        return new self(__('validation.numeric', ['attribute' => self::attributeString()]), $actual);
    }

    public static function requireBefore(string $before, string $after) {
        return new self(__('validation.before', ['attribute' => self::attributeString(), 'after' => $after]), $before);
    }

    public static function requireRange(string $value,  int $from, int $to) {
        return new self(__('validation.between.numeric', ['attribute' => self::attributeString(),'min' => strval($from), 'max' => strval($to)]), $value);
    }

    public static function invalidConcept(string $concept) {
        return self::objectNotFound('concept', $concept);
    }

    public static function invalidEntity(string $entity) {
        return self::objectNotFound('entity', $entity);
    }

    public static function invalidEntities(array $entities) {
        return self::objectNotFound('entity', $entities);
    }

    public static function invalidGeoData(string $data) {
        return new self(__('validation.invalid_geodata', ['data' => $data]));
    }

    public static function invalidUnit(string $unit) {
        return self::objectNotFound('unit', $unit);
    }

    public static function invalidDefinition(string $type, $data) {
        return new self(__('validation.definition', ['type' => $type]), $data);
    }

    public static function importNotSupported(string $type) {
        return new self(__('validation.import_not_supported', ['type' => $type]));
    }

    public static function objectNotFound(string $object) {
        return new self(__('validation.object_missing', ['object' => $object]));
    }
}
