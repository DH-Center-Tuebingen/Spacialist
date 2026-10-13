<?php

namespace App\Interfaces;

use App\Plugin;
use App\Plugin\Manifest as PluginManifest;

interface ManifestContent {
    public function retrieveManifestValues(PluginManifest $manifest): array;
    public function verifyManifest(PluginManifest $manifest): bool;
}