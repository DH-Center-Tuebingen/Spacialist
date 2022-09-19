<?php

namespace App;

class MigrationBase {
    protected $helper;

    function __construct() {
        $base = get_called_class();
        preg_match("/^App\\\\Plugins\\\\([^\\\\]+)\\\\Migration\\\\\w+/", $base, $matches);

        if(count($matches) != 2) {
            // Throw exception
            return;
        }

        $from = $matches[1];

        $this->helper = new MigrationHelper($from);
    }
}