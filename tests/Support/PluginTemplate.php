<?php

namespace Tests\Support;

use App\Plugin;

abstract class PluginTemplate {

    public Plugin $plugin;

    protected function __construct(string $name, string $uuid, string $version) {
        $this->plugin          = new Plugin();
        $this->plugin->name    = $name;
        $this->plugin->uuid    = $uuid;
        $this->plugin->version = $version;
    }

    abstract protected function getAdditionalStructure(): array;
    abstract protected function getAdditionalInfoContent(): string;
    abstract public static function create(): static;

    public static function make(string $name, string $uuid, string $version) {
        $instance = new static($name, $uuid, $version);
        return $instance;
    }
}