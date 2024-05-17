<?php
namespace App\Models;

use Exception;

abstract class Singleton {

    public static ?Singleton $instance = null;

    function __construct() {
        if (static::$instance != null) {
            throw new Exception('Singleton class');
        }
        
        static::$instance = $this;
    }

    static function init(){
        new static();
    }

    static function get() : static {
        return static::$instance;
    }
}
