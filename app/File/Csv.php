<?php

namespace App\File;

class Csv extends Parser {

    private string $delimiter;
    private bool $hasHeaderRow;
    private array $headers = [];
    private array $rows = [];


    public function __construct(bool $hasHeaderRow = false, string $delimiter = ",", array $rows = []) {
        $this->hasHeaderRow = $hasHeaderRow;
        $this->rows = $rows;
        $this->delimiter = $delimiter;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getRows() {
        return $this->rows;
    }

    public function getColumn($column) {
        $this->verifyColumnExists($column);

        $result = [];
        foreach ($this->rows as $row) {
            $result[] = $row[$column];
        }
        return $result;
    }

    public function get($column, $row) {
        $this->verifyColumnExists($column);

        return $this->rows[$row][$column];
    }

    public function reset() {
        $this->headers = [];
        $this->rows = [];
    }

    public function parse($data) {
        $this->reset();

        $lines = explode("\n", $data);
        if (count($lines) == 0) {
            return;
        }

        $this->headers = $this->parseHeaders($lines);
        $this->rows = $this->parseRows($lines);
    }


    /**
     * Parses a CSV file.
     * 
     * @param resource $fileHandle - The file handle to the CSV file
     */
    public function parseFile($fileHandle, $rowCallback = null) {
        $this->reset();

        $rowIndex = 0;
        $rows = [];
        if ($headerRow = fgetcsv($fileHandle, 0, $this->delimiter)) {

            $this->headers = $this->headerFromRow($headerRow);

            // When the headers are not named, the first row will remain in the array.
            if ($this->hasHeaderRow == false) {
                $this->appendRow($headerRow, $rows, $rowCallback, $rowIndex);
            }
            $rowIndex++;
        }

        while (($row = fgetcsv($fileHandle, 0, $this->delimiter)) !== false) {
            $this->appendRow($row, $rows, $rowCallback, $rowIndex);
            $rowIndex++;
        }

        $this->rows = $rows;
    }

    private function appendRow($row, &$rows, $rowCallback, $rowIndex) {
        $row = $this->parseRow(implode($this->delimiter, $row));
        if ($rowCallback) {
            $row = $rowCallback($row, $this->headers, $rows, $rowIndex);
        }
        $rows[] = $row;
    }

    /**
     * Gets the rows from the CSV file.
     * 
     * @param array $lines - The lines of the CSV file
     * @return array $rows - All rows as associative arrays using the headers as keys and the values from the line as values
     */
    private function parseRows($lines) {
        $rows = [];
        foreach ($lines as $line) {
            $this->rows[] = $this->parseRow($line);
        }
        return $rows;
    }

    /**
     * Gets a single row from a CSV line string.
     * 
     * @param string $line - The line from the CSV file
     * @return array $row - Associative array using the headers as keys and the values from the line as values
     */
    private function parseRow($line) {
        $row = [];
        $arr = $this->getcsv($line);
        for ($i = 0; $i < count($arr); $i++) {
            $row[$this->headers[$i]] = $arr[$i];
        }
        return $row;
    }

    /**
     * Gets the headers from the CSV file.
     * 
     * If there is no header (hasHeaderRow = false), it will return an array of numbers (1, 2, 3, ...)
     * and the array will not be changed.
     * 
     * If the file has a header row (hasHeaderRow = false), use the first row as the headers
     * and said row will be removed from the array.
     * 
     * @param array $lines - The lines of the CSV file
     * @return array $headers - The headers of the CSV file
     */
    private function parseHeaders($lines) {
        $row = $this->getcsv($lines[0]);

        if ($this->hasHeaderRow) {
            array_shift($lines);
        }

        return $this->headerFromRow($row);
    }

    private function headerFromRow($row) {
        if ($this->hasHeaderRow) {
            return $row;
        } else {
            return $this->generateNumberHeaders($row);
        }
    }

    private function generateNumberHeaders($row) {
        $headers = [];

        for ($i = 1; $i <= count($row); $i++) {
            $headers[] = $i;
        }

        return $headers;
    }

    /**
     * Helper function to get the CSV from a line.
     * Always using the correct delimiter.
     */
    private function getcsv($line) {
        return str_getcsv($line, separator: $this->delimiter);
    }

    private function verifyColumnExists($column) {
        if (!in_array($column, $this->headers)) {
            throw new \Exception("Column '$column' does not exist.");
        }
    }
}
