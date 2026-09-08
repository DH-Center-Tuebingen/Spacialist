<?php

namespace Tests\Feature\Plugin;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\Support\PluginDirectoryGenerator;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;

class ApiPluginDiscoveryTest extends TestCase {

    private array $manualCleanupDirs = [];
    private ?PluginGenerator $generator = null;

    protected function setUp(): void {
        parent::setUp();
        Carbon::setTestNow('2020-07-20 10:15:30');
    }

    private function ensureTeardown() {
        if($this->generator !== null) {
            $this->generator->tearDown();
            $this->generator = null;
        }

        foreach($this->manualCleanupDirs as $dir) {
            PluginDirectoryGenerator::cleanup($dir);
        }
        $this->manualCleanupDirs = [];
    }

    protected function tearDown(): void {
        parent::tearDown();
        Carbon::setTestNow();
        $this->ensureTeardown();
    }

    /**
     * Places a plugin on the filesystem without creating a corresponding
     * database record, simulating a plugin that has never been discovered.
     */
    private function mockUndiscoveredPlugin(PluginTemplate $template): string {
        $template->generate("plugin.xml");
        $pluginDir = PluginGenerator::getPluginDirectory($template->plugin->name);
        $this->manualCleanupDirs[] = PluginDirectoryGenerator::mockPluginDirectory($template, $pluginDir);
        return $pluginDir;
    }

    function testRefreshDiscoversNewPluginFromFilesystem() {
        $template = new PluginTemplate(
            name: 'DiscoveredPlugin',
            version: '1.0.0'
        );
        $template->addBasic();
        $this->mockUndiscoveredPlugin($template);

        $this->assertDatabaseMissing('plugins', ['name' => 'DiscoveredPlugin']);

        $response = $this->userRequest()->post('/api/v1/plugin/refresh');
        $response->assertStatus(200);
        $this->assertDatabaseHas('plugins', [
            'name' => 'DiscoveredPlugin',
            'version' => '1.0.0',
        ]);
    }

    function testRefreshRequiresPluginWritePermission() {
        $template = new PluginTemplate(
            name: 'DiscoveredPlugin',
            uuid: Str::uuid()->toString(),
            version: '1.0.0'
        );
        $template->addBasic();
        $this->mockUndiscoveredPlugin($template);

        $this->useUserWithPermissions(['plugin_read']);
        $response = $this->userRequest()->post('/api/v1/plugin/refresh');

        $response->assertStatus(403);
        $this->assertDatabaseMissing('plugins', ['name' => 'DiscoveredPlugin']);
    }

    function testRefreshInfoUpdatesExistingPluginMetadataFromManifest() {
        $template = (new PluginTemplate(
            name: 'RefreshablePlugin',
            uuid: Str::uuid()->toString(),
            version: '1.0.0'
        ))->addBasic()->skipInstall()->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $this->assertDatabaseHas('plugins', [
                'name' => 'RefreshablePlugin',
                'version' => '1.0.0',
            ]);

            // Overwrite the plugin's manifest on disk with a bumped version, simulating
            // a manual file update without going through the regular upload flow.
            $updatedTemplate = new PluginTemplate(
                name: 'RefreshablePlugin',
                uuid: $template->plugin->uuid,
                version: '2.0.0'
            );
            $updatedTemplate->addBasic()->generate("plugin.xml");
            PluginDirectoryGenerator::mockPluginDirectory($updatedTemplate, PluginGenerator::getPluginDirectory('RefreshablePlugin'));

            $response = $this->userRequest()->post("/api/v1/plugin/refresh_info/{$template->plugin->id}");

            $response->assertStatus(200);
            $this->assertDatabaseHas('plugins', [
                'id' => $template->plugin->id,
                'version' => '2.0.0',
            ]);
        });
    }
}
