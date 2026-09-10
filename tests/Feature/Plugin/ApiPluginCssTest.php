<?php

namespace Tests\Feature\Plugin;

use App\Services\PluginManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;

class ApiPluginCssTest extends TestCase {

    private const FAKE_STORAGE = 'fake_css_storage';
    private const PLUGIN_NAME = 'CssPlugin';
    private const PLUGIN_UUID = '00000000-0000-0000-0000-000000000004';

    private ?PluginGenerator $generator = null;

    static function defaultCssTemplate(array $cssFiles = []): PluginTemplate {
        $template = new PluginTemplate(
            name: static::PLUGIN_NAME,
            uuid: static::PLUGIN_UUID,
            version: "1.0.0"
        );
        $template->addBasic();

        foreach($cssFiles as $file) {
            $template->addFile($file, "body { color: red; }");
        }

        if(count($cssFiles) > 0) {
            $template->addXml("css", "file", array_map(fn($src) => ['src' => $src], $cssFiles));
        }

        return $template;
    }

    protected function setUp(): void {
        parent::setUp();
        Storage::fake(static::FAKE_STORAGE);
        app(PluginManager::class)->cssService->setDisk(static::FAKE_STORAGE);
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
    
    function testInstallSkipsEmptyCssPath() {
        $template = self::defaultCssTemplate()->addXml("css", "file", [['src' => '']])->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseCount('plugin_service_css_files', 0);
        });
    }

    function testInstallPublishesCssFileAndCreatesRecord() {
        $template = self::defaultCssTemplate(['style.css'])->created()->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            Storage::disk(static::FAKE_STORAGE)->assertMissing("plugin_css/cssplugin-00000000-0000-0000-0000-000000000004-style.css");
            
            $this->assertDatabaseMissing('plugin_service_css_files', [
                'plugin_id' => $template->plugin->id,
            ]);
            
            $response = $this->userRequest()
            ->post("/api/v1/plugin/install/{$template->plugin->id}");
            
            $response->assertStatus(200);

            $this->assertDatabaseHas('plugin_service_css_files', [
                'plugin_id' => $template->plugin->id,
                'src' => 'style.css',
            ]);

            $styles = $response->json('styles');
            $this->assertCount(1, $styles);
            $cssResponse = $this->userRequest()->get('/' . $styles[0]);
            $cssResponse->assertStatus(200);
            Storage::disk(static::FAKE_STORAGE)->assertExists("plugin_css/cssplugin-00000000-0000-0000-0000-000000000004-style.css");
        });
    }

    function testUninstallRemovesCssRecordAndUnpublishesFile() {
        $template = self::defaultCssTemplate(['style.css'])->generate("plugin.xml");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            
            $this->assertDatabaseHas('plugin_service_css_files', [
                'plugin_id' => $template->plugin->id,
                'src' => 'style.css',
                ]);
            Storage::disk(static::FAKE_STORAGE)->assertExists("plugin_css/cssplugin-00000000-0000-0000-0000-000000000004-style.css");
                
            $response = $this->userRequest()
                ->post("/api/v1/plugin/uninstall/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseMissing('plugin_service_css_files', [
                'plugin_id' => $template->plugin->id,
            ]);
            Storage::disk(static::FAKE_STORAGE)->assertMissing("plugin_css/cssplugin-00000000-0000-0000-0000-000000000004-style.css");
        });
    }
}
