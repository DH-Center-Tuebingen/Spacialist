<?php

namespace Tests\Feature\Plugin;

use App\Services\PluginManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;

class ApiPluginScriptTest extends TestCase {

    private const FAKE_STORAGE = 'fake_script_storage';
    private const PLUGIN_NAME = 'ScriptPlugin';
    private const PLUGIN_UUID = '00000000-0000-0000-0000-000000000007';

    private ?PluginGenerator $generator = null;

    static function defaultScriptTemplate(): PluginTemplate {
        $template = new PluginTemplate(
            name: static::PLUGIN_NAME,
            uuid: static::PLUGIN_UUID,
            version: "1.0.0"
        );
        // addBasic() already publishes the mandatory js/script.js file.
        return $template->addBasic();
    }

    protected function setUp(): void {
        parent::setUp();
        Storage::fake(static::FAKE_STORAGE);
        app(PluginManager::class)->scriptService->setDisk(static::FAKE_STORAGE);
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

    function testInstallPublishesScriptAndMakesItDownloadable() {
        $template = self::defaultScriptTemplate()->skipInstall()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            Storage::disk(static::FAKE_STORAGE)->assertMissing("plugins/scriptplugin-00000000-0000-0000-0000-000000000007.js");
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            $scripts = $response->json('scripts');
            $this->assertCount(1, $scripts);
            $this->assertStringContainsString($template->plugin->slugName() . '-' . $template->plugin->uuid . '.js', $scripts[0]);

            $scriptResponse = $this->userRequest()->get('/' . $scripts[0]);
            $scriptResponse->assertStatus(200);
            $scriptResponse->assertHeader('Content-Type', 'application/javascript');
            $this->assertStringContainsString(
                'Hello from ' . static::PLUGIN_NAME,
                file_get_contents($scriptResponse->baseResponse->getFile()->getPathname())
            );
            
            Storage::disk(static::FAKE_STORAGE)->assertExists("plugins/scriptplugin-00000000-0000-0000-0000-000000000007.js");
        });
    }

    function testUninstallRemovesPublishedScript() {
        $template = self::defaultScriptTemplate()->install()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            Storage::disk(static::FAKE_STORAGE)->assertExists("plugins/scriptplugin-00000000-0000-0000-0000-000000000007.js");
            $scriptUrl = app(\App\Services\Plugin\ScriptService::class)->getUrl($template->plugin);
            $this->userRequest()->get('/' . $scriptUrl)->assertStatus(200);

            $response = $this->userRequest()
                ->post("/api/v1/plugin/uninstall/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->userRequest()->get('/' . $scriptUrl)->assertStatus(404);
            Storage::disk(static::FAKE_STORAGE)->assertMissing("plugins/scriptplugin-00000000-0000-0000-0000-000000000007.js");
        });
    }

    
    function testInstallFailsWhenScriptFileIsMissing() {
        $template = new PluginTemplate(
            name: static::PLUGIN_NAME,
            uuid: static::PLUGIN_UUID,
            version: "1.0.0"
        );
        $template->addBasicChangelog()->skipInstall()->generate("plugin.xml");
        
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(422);
        });
    }
        
}
