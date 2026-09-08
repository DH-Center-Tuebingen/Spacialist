<?php

namespace Tests\Feature\Plugin;

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;

class ApiPluginRouteTest extends TestCase {

    private const PLUGIN_NAME = 'RoutePlugin';
    private const PLUGIN_UUID = '00000000-0000-0000-0000-000000000006';

    private ?PluginGenerator $generator = null;

    static function defaultRouteTemplate(?string $src = null): PluginTemplate {
        $template = new PluginTemplate(
            name: static::PLUGIN_NAME,
            uuid: static::PLUGIN_UUID,
            version: "1.0.0"
        );
        $template->addBasic();

        if($src !== null) {
            $template->addXml("routes", null, [['src' => $src]]);
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
    
    function testInstallWithoutRoutesFile() {
        $template = self::defaultRouteTemplate()
            ->skipInstall()
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);
        });
    }

    function testInstallEmptyLegacyRoutesFile() {
        $template = self::defaultRouteTemplate()
            ->addFile('lib/App/routes.php', "<?php // no-op routes file")
            ->skipInstall()
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);
            $this->assertDatabaseCount('plugin_service_routes', 0);
        });
    }

    function testInstallLegacyRoutesFile() {
        $template = self::defaultRouteTemplate()
            ->addFile('lib/App/routes.php', "<?php 
                use Illuminate\Support\Facades\Route;
                
                Route::get('/', function() {
                    return 'Hello, world!';
                });
            ")
            ->generate("plugin.xml");

        info("TEST LEGACY");
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
        // foreach(Route::getRoutes() as $value) {
        //     info(json_encode($value));
        // }
        // $endpoint = static::PLUGIN_NAME;
        // $response = $this->userRequest()
        //     ->post("/api/v1/{$endpoint}");

        // $response->assertStatus(200);
        }, true);
    }
    

    function testUninstallRemovesRouteRecord() {
        $template = self::defaultRouteTemplate('lib/App/routes.php', 'web')
            ->addFile('lib/App/routes.php', "<?php // no-op routes file")
            ->install()
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $this->assertDatabaseHas('plugin_service_routes', ['plugin_id' => $template->plugin->id]);

            $response = $this->userRequest()
                ->post("/api/v1/plugin/uninstall/{$template->plugin->id}");

            $response->assertStatus(200);

            $this->assertDatabaseMissing('plugin_service_routes', ['plugin_id' => $template->plugin->id]);
        });
    }

    function testInstallRequiresPluginWritePermission() {
        $template = self::defaultRouteTemplate('lib/App/routes.php')
            ->addFile('lib/App/routes.php', "<?php // no-op routes file")
            ->skipInstall()
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $this->useUserWithPermissions(['plugin_read']);
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(403);

            $this->assertDatabaseCount('plugin_service_routes', 0);
        });
    }
}
