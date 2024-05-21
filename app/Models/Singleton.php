<?php

namespace App\Models;

abstract class Singleton {

    public static ?Singleton $instance = null;

    protected function __construct() {
        static::$instance = $this;
    }

    static function get(): static {
        if (static::$instance != null) {
            return static::$instance;
        }
        return new static();
    }
}
