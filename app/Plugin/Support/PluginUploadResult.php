<?php

namespace App\Plugin\Support;

use App\Plugin;

class PluginUploadResult {
    public function __construct(
        public readonly string $pluginName,
        public readonly ?Plugin $plugin = null,
    ) {}
    
    public function isUpdate(): bool {
        return $this->plugin !== null;
    }
}