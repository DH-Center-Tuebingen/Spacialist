<?php

namespace App;

use App\Exceptions\TableAlreadyExistsException;
use App\Exceptions\TableDoesNotExistException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrationOption
{
    private $options;

    function __construct($options)
    {
        $this->options = $options;
    }

    function exists($key) {
        return array_key_exists($key, $this->options);
    }

    public function getType() {
        return $this->options['type'];
    }

    public function getDefault() {
        return $this->options['default'];
    }

    public function getComment() {
        return $this->options['comment'];
    }

    public function getStrLength() {
        return $this->exists('length') ? $this->options['length'] : null;
    }

    public function getPrecision() {
        return $this->exists('precision') ? $this->options['precision'] : 8;
    }

    public function getScale() {
        return $this->exists('scale') ? $this->options['scale'] : 2;
    }

    public function isPrimary() {
        return $this->exists('primary') && $this->options['primary'] === true;
    }

    public function isUnsigned() {
        return $this->exists('unsigned') && $this->options['unsigned'] === true;
    }

    public function isUnique() {
        return $this->exists('unique') && $this->options['unique'] === true;
    }

    public function isNullable() {
        return $this->exists('nullable') && $this->options['nullable'] === true;
    }

    public function withTz() {
        return $this->exists('with_tz') && $this->options['with_tz'] === true;
    }

    public function hasDefault() {
        return $this->exists('default');
    }

    public function hasComment() {
        return $this->exists('comment') && !empty($this->options['comment']);
    }
}