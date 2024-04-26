<?php

namespace App\Exceptions;

use Exception;
use App\Exceptions\Structs\ImportExceptionStruct;

class ImportException extends Exception {

    private ImportExceptionStruct $data;

    public function __construct(String $message, ImportExceptionStruct $data = null) {
        parent::__construct($message);

        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function getObject() {
        return [
            "error" => __($this->getMessage()),
            "data" => $this->data
        ];
    }
}
