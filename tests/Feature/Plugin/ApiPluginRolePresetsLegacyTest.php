<?php

namespace Tests\Feature\Plugin;

use Tests\Support\PluginTemplate;

class ApiPluginRolePresetsLegacyTest extends ApiPluginRolePresetsTest {
    protected string $filePath = 'role-presets.json';

    protected function addManifestEntryToTemplate(PluginTemplate $template, array $files)
    {
        // The legacy version had no manifest entry for role presets.
    }
    
    function testMultipleFiles() {
        // Not applicable to legacy implementation: only one file is supported.
        $this->assertTrue(true); 
    }
}