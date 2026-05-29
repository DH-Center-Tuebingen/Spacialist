<?php

namespace Tests\Feature\Plugin;

use Tests\Support\Templates\MigrationTemplate;
/**
 * Tests the same functionalty as ApiPluginMigrationTest, but using the legacy path that is not explicitly defined in the manifest.
 */
class ApiPluginMigrationLegacyTest extends ApiPluginMigrationTest {

    protected function modifyDefaultTemplate(MigrationTemplate $template): MigrationTemplate {
        // Don't set the migration path in the manifest, so that the default legacy path is used.
        return $template;
    }
}
