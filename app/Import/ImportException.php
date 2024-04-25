<?php

namespace App\Import;

use Exception;

class ImportException extends Exception {

    private ImportExceptionData $data;

    public function __construct(String $message, ImportExceptionData $data = null) {
        parent::__construct($message);

        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function getObject() {
        info($this->message);
        return [
            "error" => __($this->getMessage()),
            "data" => $this->data
        ];
    }
}
