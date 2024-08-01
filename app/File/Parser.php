<?php

namespace App\File;

abstract class Parser {
    abstract public function parse(string $filePath, callable $rowCallback);
}
