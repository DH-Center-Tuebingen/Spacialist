<?php

namespace App\Interfaces;

use App\Plugin;
use App\Plugin\PluginManifest;

interface ManifestContent {
    public function retrieveManifestValues(PluginManifest $manifest): array;
    public function verifyManifest(PluginManifest $manifest): bool;
    public function createFromJson(Plugin $plugin, array $json): void;
}