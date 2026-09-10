<?php

namespace Tests\Feature\Plugin;

use App\Services\PluginManager;
use Carbon\Carbon;
use Tests\Support\PluginGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;

class ApiPluginRouteTest extends TestCase {

    const ROUTES_DATABASE = "plugin_service_routes";
    const PLUGIN_NAME = 'RoutePlugin';
    const PLUGIN_SLUG = "routeplugin";
    const PLUGIN_UUID = '00000000-0000-0000-0000-000000000006';

    const HELLO_WORLD_PHP = "<?php 
use Illuminate\Support\Facades\Route;

Route::get('', function() {
    return 'Hello, world!';
});
";

    private ?PluginGenerator $generator = null;
    protected string $filePath = 'custom/dir/routes.php';

    function defaultRouteTemplate(): PluginTemplate {
        $template = new PluginTemplate(
            name: self::PLUGIN_NAME,
            uuid: self::PLUGIN_UUID,
            version: "1.0.0"
        );
        $template->addBasic();
        $this->addManifestEntryToTemplate($template);
        return $template;
    }
    
    protected function addManifestEntryToTemplate(PluginTemplate $template){
        $template->addXml("routes", null, [['src' => $this->filePath]]);
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

    // This is kinda ugly, but in a normal environment the next request to a route
    // would ever happen after the program is called again. As we are in the same process
    // inside the testing environment, we need to call those routes to be registered.
    // 'Restarting' the entire app was not an option, as it lost all the data that
    // was already put inside the database. [SO]
    private function reloadPluginRoutes() {
        app(PluginManager::class)->routeService->mapRoutes();
    }

    function testInstallWithoutRoutesFile() {
        $template = $this->defaultRouteTemplate()
            ->created()
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);
        });
    }

    function testInstallEmptyRoutesFile() {
        $template = $this->defaultRouteTemplate()
            ->addFile($this->filePath, "<?php // no-op routes file")
            ->created()
            ->generate("plugin.xml");
            
        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $this->assertDatabaseCount(self::ROUTES_DATABASE, 0);
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);
            $this->assertDatabaseCount(self::ROUTES_DATABASE, 1);
        }, true);
    }

    function testInstallRoutesFile() {
        $template = $this->defaultRouteTemplate()
            ->addFile($this->filePath, self::HELLO_WORLD_PHP)
            ->created()
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $response = $this->userRequest()
                ->post("/api/v1/plugin/install/{$template->plugin->id}");

            $response->assertStatus(200);
            $this->assertDatabaseCount(self::ROUTES_DATABASE, 1);
            $this->assertDatabaseHas(self::ROUTES_DATABASE, [
                'src' => $this->filePath,
                'plugin_name' => self::PLUGIN_NAME,
                'plugin_slug' => self::PLUGIN_SLUG
            ]);
        });
    }

    function testUninstallRemovesRouteRecord() {
        $template = self::defaultRouteTemplate()
            ->addFile($this->filePath, "<?php // no-op routes file")
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $this->assertDatabaseHas(self::ROUTES_DATABASE, ['plugin_id' => $template->plugin->id]);

            $response = $this->userRequest()
                ->post("/api/v1/plugin/uninstall/{$template->plugin->id}");

            $response->assertStatus(200);
            $this->assertDatabaseMissing(self::ROUTES_DATABASE, ['plugin_id' => $template->plugin->id]);
        });
    }

    function testRouteIsAccessible() {
        $template = self::defaultRouteTemplate()
            ->addFile($this->filePath, self::HELLO_WORLD_PHP)
            ->installed()
            ->generate("plugin.xml");

        $this->generator = PluginGenerator::with([$template], function () use ($template) {
            $this->reloadPluginRoutes();

            $response = $this->userRequest()
                ->get("/api/v1/routeplugin");

            $response->assertStatus(200);
            $response->assertContent("Hello, world!");
        });
    }
}

