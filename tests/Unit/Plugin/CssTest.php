<?php
namespace Tests\Unit\Plugin;

use App\Models\Plugin\CssFile;
use App\Plugin;
use App\Plugin\PluginManifest;
use App\Services\Plugin\CssService;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;

class CssTest extends TestCase {
    protected Plugin $plugin;


    public function generateXml(array $files): string {
        if($files === null || count($files) === 0) {
            return "";
        }
        $cssXml = "";
        foreach($files as $file) {
            $cssXml .= "    <file src=\"{$file['src']}\"";
            $cssXml .= " />\n";
        }
        return "<css>\n" . $cssXml . "</css>";
    }

    public function mockCssPlugin($cssEntries = [], string $name = "CssTemplate", string $uuid = "123e4567-e89b-12d3-a456-426614174010", string $version = "1.0.0"): PluginTemplate {
        $template = new PluginTemplate(
            name: $name,
            uuid: $uuid,
            version: $version
        );

        $template->addBasic();
        
        $template->addXml("css", "file", array_map(function ($entry) {
            return [
                "src" => $entry
            ];
        }, $cssEntries));

        return $template->generate();
    }

    public function testNoCssEntriesInManifest() {
        $template = $this->mockCssPlugin();
        $generator = new PluginGenerator([$template]);
        $generator->use(function () use ($template) {
            $manifest = PluginManifest::fromPlugin($template->plugin);
            app(CssService::class)->verifyManifest($manifest);
            $this->assertEquals([], CssFile::all()->toArray());
        }, true);
    }

    public function testSingleCssEntryInManifest() {
        $template = $this->mockCssPlugin($cssEntries = ["path/to/file.css"]);
        $generator = new PluginGenerator([$template]);
        $generator->use(function () use ($template) {
            $plugin = $template->plugin;
            $manifest = PluginManifest::fromPlugin($template->plugin);
            app(CssService::class)->createFromManifest($plugin, $manifest);
            $this->assertDatabaseHas('plugin_service_css_files', [
                "src" => "path/to/file.css"
            ]);

        }, true);
    }

    public function testMultipleCssEntriesInManifest() {
        $template = $this->mockCssPlugin($cssEntries = ["path/to/file_1.css", "path/to/file_2.css"]);
        $generator = new PluginGenerator([$template]);
        $generator->use(function () use ($template) {

            $plugin = $template->plugin;
            $manifest = PluginManifest::fromPlugin($template->plugin);
            app(CssService::class)->createFromManifest($plugin, $manifest);

            $this->assertDatabaseHas('plugin_service_css_files', [
                "src" => "path/to/file_1.css"
            ]);
            $this->assertDatabaseHas('plugin_service_css_files', [
                "src" => "path/to/file_2.css"
            ]);

        }, true);
    }
}
