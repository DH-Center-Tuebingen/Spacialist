<?php

namespace App\Patterns;

abstract class Singleton {

    protected static $instance = null; // Must be redefined in the child class.

    protected function __construct() {
        static::$instance = $this;
    }

    final static function get() {
        if(static::$instance != null) {
            return static::$instance;
        }
        return new static();
    }
}
