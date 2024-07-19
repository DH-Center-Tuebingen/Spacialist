<?php

namespace App\PluginResources\Interfaces;

use App\PluginResources\Plugin;

interface Installable
{    
    public static function install(Plugin $plugin);
    public static function uninstall(Plugin $plugin);
}