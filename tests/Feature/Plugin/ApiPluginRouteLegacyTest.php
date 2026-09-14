<?php

namespace Tests\Feature\Plugin;

use Tests\Support\PluginTemplate;

/**
 *  Use the ApiPluginRouteTest and just override the filepath and legacy option.
 */
class ApiPluginRouteLegacyTest extends ApiPluginRouteTest {
    protected string $filePath = 'routes/api.php';
    protected function addManifestEntryToTemplate(PluginTemplate $template){
        // For legacy routes, we might not need to add the manifest entry.
    }
}

