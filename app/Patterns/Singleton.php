<?php

namespace App\Patterns;

abstract class Singleton {

    // NOTE: The problem in PHP is that static properties are shared between child classes, 
    // so we need to store the instance in an array with the class name as key.
    
    /**
    *  The instances of the singleton classes, stored with the class name as key.
    * This is necessary because static properties are shared between child classes in PHP.
    *
    * @var array<string, static> - An array that holds the instances of the singleton classes, indexed by their class names.
    */
    protected static array $instances = [];
    
    protected function __construct() {}

    /**
     * Returns the singleton instance of the class. If the instance does not exist, it creates a new one.
     */
    final public static function get(): static {
        if(!isset(static::$instances[static::class])) {
            static::$instances[static::class] = new static();
        }
        return static::$instances[static::class];
    }
}
