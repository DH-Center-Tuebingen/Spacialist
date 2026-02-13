<?php
namespace Tests\Cases\Plugin;

use App\Plugin;
use Tests\Support\PluginDirectoryGenerator;
use Tests\TestCase;

class PluginHookTestCase extends TestCase
{
    protected Plugin $plugin;

    static string $pluginDir = '';

    public static function tearDownAfterClass(): void
    {
        PluginDirectoryGenerator::cleanup(static::$pluginDir);
        static::$pluginDir = '';
        parent::tearDownAfterClass();
    }

    protected function mockupPluginDirectory(): void {
        static::$pluginDir = PluginDirectoryGenerator::mockPluginDirectory(
            $this->plugin,
            [
                'Hooks' => [
                    "AddPreData.php" => <<<'ADD_PRE_DATA'
<?php
namespace App\Plugins\HookPlugin\Hooks;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AddPreData {
    public static function apply(Request $request, JsonResponse $response): void {
        $data = $response->getData(true);
        $data['hook-plugin-message'] = 'Executed';
        $response->setData($data);
    }
}
ADD_PRE_DATA,
                ],
            ],
            "<hooks>\n<hook on=\"api/v1/pre\" src=\"Hooks\\AddPreData@apply\" />\n</hooks>"
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createPlugin();
        if(!static::$pluginDir) {
            $this->mockupPluginDirectory();
        }
        $this->mockupPluginDirectory();
        $this->plugin->save();
        $this->plugin->handleInstallation();
    }

    protected function createPlugin()
    {
        $this->plugin          = new Plugin();
        $this->plugin->name    = 'HookPlugin';
        $this->plugin->uuid    = '123e4567-e89b-12d3-a456-426614174000';
        $this->plugin->version = '1.0.0';
    }

    protected function tearDown(): void
    {
        $this->plugin->delete();
        parent::tearDown();
    }

}
