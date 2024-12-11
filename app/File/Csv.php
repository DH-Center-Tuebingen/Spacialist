<?php

namespace App\File;

class Csv extends Parser {

    private string $delimiter;
    private bool $hasHeaderRow;
    private array $headers = [];
    private string $encoding = 'UTF-8';
    private int $rows = 0;

    public function __construct(bool $hasHeaderRow = false, string $delimiter = ",", string $encoding = 'UTF-8') {
        $this->hasHeaderRow = $hasHeaderRow;
        $this->delimiter = $delimiter;
        $this->encoding = $encoding;
    }

    public function getHeaders() {
        return $this->headers;
    }



    /**
     * Parses a CSV file.
     * 
     * @param resource $fileHandle - The file handle to the CSV file
     */
    public function parse($fileHandle, $rowCallback) {
        $rowIndex = 0;
        $this->rows = 0;
        // We don't use the fgetcsvfunction to change the file encoding to UTF-8.
        while(($row = fgets($fileHandle)) !== false) {
            $this->rows++;
            if($this->hasHeaderRow && $rowIndex === 0) {
                $rowIndex++;
                continue;
            }

            $row = $this->toUtf8($row);
            $row = $this->parseRow($row);
            $rowCallback($row, $rowIndex, $this->headers);
            $rowIndex++;
        }
    }

    /**
     * Gets the headers from the CSV file.
     * 
     * If there is no header (hasHeaderRow = false), it will return an array of numbers (1, 2, 3, ...)
     * and the array will not be changed.
     * 
     * @param array $lines - The lines of the CSV file
     * @return array $headers - The headers of the CSV file
     */
    public function parseHeaders($fileHandle) {
        $headers = null;

        $row = fgets($fileHandle);
        if(!$row) {
            throw new \Exception("File is empty.");
        }

        if($this->hasHeaderRow) {
            $utf8Line = $this->toUtf8($row);
            $headers = $this->fromCsvLine($utf8Line);
            $headers = array_map(fn ($header) => trim($header), $headers);
        } else {
            $headers = $this->generateNumberHeaders($row);
        }

        rewind($fileHandle);
        $this->headers = $headers;
        return $headers;
    }

    private function generateNumberHeaders($row) {
        $headers = [];
        $parts = explode($this->delimiter, $row);
        for($i = 1; $i <= count($parts); $i++) {
            $headers[] = "#$i";
        }

        return $headers;
    }

    /**
     * Gets a single row from a CSV line string.
     * 
     * @param string $line - The line from the CSV file
     * @return array $row - Associative array using the headers as keys and the values from the line as values
     */
    private function parseRow($line) {
        if(empty($this->headers)) {
            throw new \Exception("Headers are not set. Run parseHeaders first.");
        }

        $row = [];

        $arr = $this->fromCsvLine($line);
        for($i = 0; $i < count($arr); $i++) {
            $row[$this->headers[$i]] = trim($arr[$i]);
        }
        return $row;
    }


    /**
     * Converts the data to UTF-8 if it is not already.
     * 
     * @param array|string $data - The data to convert
     * @return array|string $data - The converted data
     */
    private function toUtf8(array|string $data): array|string {
        if($this->isUtf8()) return $data;

        $tgt = 'UTF-8';
        if(is_array($data)) {
            $data = array_map(fn ($str) => iconv($this->encoding, $tgt, $str), $data);
        } else {
            $data = iconv($this->encoding, $tgt, $data);
        }

        return $data;
    }

    private function isUtf8(): bool {
        return $this->encoding == 'UTF-8';
    }


    public function getRows() {
        return $this->rows;
    }

    public function getDataRows() {
        return $this->rows - ($this->hasHeaderRow ? 1 : 0);
    }

    /**
     * Helper function to get the CSV from a line.
     * Always using the correct delimiter.
     */
    private function fromCsvLine($line) {
        return str_getcsv($line, separator: $this->delimiter);
    }

    private function verifyColumnExists($column) {
        if(!in_array($column, $this->headers)) {
            throw new \Exception("Column '$column' does not exist.");
        }
    }
}
