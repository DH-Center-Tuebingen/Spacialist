<?php

namespace App\Exceptions;

use Exception;
use App\Exceptions\Structs\AttributeImportExceptionStruct;
use App\Exceptions\Structs\ImportExceptionStruct;

class AttributeImportException extends Exception {

    private AttributeImportExceptionStruct $data;

    public function __construct(String $message, AttributeImportExceptionStruct $data = null) {
        parent::__construct($message);

        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function toImportExceptionObject(int $count, string $entry) {
        $ies = new ImportExceptionStruct(
            count: $count,
            entry: $entry,
            on: $this->data->type,
            on_index: $this->data->columnIndex,
            on_value: $this->data->columnValue,
            on_name: $this->data->columnName
        );

        return [
            "error" => __($this->getMessage()),
            "data" => $ies
        ];
    }
}
