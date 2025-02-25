<?php

namespace App\Exceptions;

use Exception;

class CsvColumnMismatchException extends Exception {
    public $headerLine;
    public $dataLine;
    public $headerColumns;
    public $dataColumns;

    public function __construct(string $headerLine, string $dataLine, int $headerColumns, int $dataColumns) {
        $this->headerLine = $headerLine;
        $this->dataLine = $dataLine;
        $this->headerColumns = $headerColumns;
        $this->dataColumns = $dataColumns;
    }
}