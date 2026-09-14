<?php

namespace Tests\Feature\Plugin;

use Carbon\Carbon;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\PluginTestCase;

class ApiPluginPermissionTest extends PluginTestCase {

    private const PLUGIN_NAME = 'PermissionPlugin';
    private const PLUGIN_UUID = '00000000-0000-0000-0000-000000000005';

    private ?PluginGenerator $generator = null;

    static function defaultPermissionTemplate(?array $permissions = null, ?string $customSrc = null): PluginTemplate {
        $template = new PluginTemplate(
            name: static::PLUGIN_NAME,
            uuid: static::PLUGIN_UUID,
            version: "1.0.0"
        );
        $template->addBasic();

        if($permissions !== null) {
            $path = $customSrc ?? 'App/permissions.json';
            $template->addFile($path, json_encode($permissions));

            if($customSrc !== null) {
                $template->addXml("permissions", null, [['src' => $customSrc]]);
            }
        }

        return $template;
    }

    protected function setUp(): void {
        parent::setUp();
        Carbon::setTestNow('2020-07-20 10:15:30');
    }

    private function ensureTeardown() {
        if($this->generator !== null) {
            $this->generator->tearDown();
            $this->generator = null;
        }
    }

    protected function tearDown(): void {
        parent::tearDown();
        Carbon::setTestNow();
        $this->ensureTeardown();
    }

    function testInstallAddsPermissionsFromDefaultManifestPath() {
        $permissions = [
            'permtest_group' => [
                ['name' => 'read', 'display_name' => 'Read', 'description' => 'Can read'],
                ['name' => 'write', 'display_name' => 'Write', 'description' => 'Can write'],
            ],
        ];
        $template = self::defaultPermissionTemplate($permissions)->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseHas('permissions', [
                'name' => 'permtest_group_read',
                'display_name' => 'Read',
                'description' => 'Can read',
                'guard_name' => 'web',
            ]);
            $this->assertDatabaseHas('permissions', [
                'name' => 'permtest_group_write',
                'display_name' => 'Write',
                'description' => 'Can write',
                'guard_name' => 'web',
            ]);
        });
    }

    function testInstallAddsPermissionsFromCustomManifestPath() {
        $permissions = [
            'permtest_custom' => [
                ['name' => 'read', 'display_name' => 'Read', 'description' => 'Can read'],
            ],
        ];
        $template = self::defaultPermissionTemplate($permissions, 'Custom/permissions.json')->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseHas('permissions', [
                'name' => 'permtest_custom_read',
            ]);
        });
    }

    function testInstallWithMissingPermissionsFileCreatesNone() {
        $template = self::defaultPermissionTemplate()->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseMissing('permissions', [
                'name' => 'permtest_group_read',
            ]);
        });
    }

    function testUninstallDoesNotRemovePermissions() {
        $permissions = [
            'permtest_kept' => [
                ['name' => 'read', 'display_name' => 'Read', 'description' => 'Can read'],
            ],
        ];
        $template = self::defaultPermissionTemplate($permissions)->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $this->assertDatabaseHas('permissions', ['name' => 'permtest_kept_read']);

            $response = $this->userRequest()
                ->post("/api/v1/plugin/uninstall/{$template->plugin->id}");

            $response->assertStatus(200);

            // Permissions are intentionally kept across uninstalls so roles don't lose
            // their assignments when a plugin is merely toggled off.
            $this->assertDatabaseHas('permissions', ['name' => 'permtest_kept_read']);
        });
    }
}
