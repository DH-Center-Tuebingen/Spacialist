<?php

namespace App\PluginResources;

class Module {
    
    private $plugin;
    
    public static function use($plugin) {
        return new static($plugin);
    }
    
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;    
    }
    
    public function getPlugin(){
        return $this->plugin;
    }
}