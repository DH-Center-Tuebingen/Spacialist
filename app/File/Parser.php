<?php

namespace App\File;

abstract class Parser {
    abstract public function parse($filePath, callable $rowCallback);
}
