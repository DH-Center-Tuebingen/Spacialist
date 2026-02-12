<?php
namespace Tests\Cases\Plugin;

use App\Plugin;
use Tests\Support\PluginDirectoryGenerator;
use Tests\TestCase;

class PluginHookTestCase extends TestCase
{
    protected Plugin $plugin;

    public static function tearDownAfterClass(): void
    {
        PluginDirectoryGenerator::cleanup();
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createPlugin();
        PluginDirectoryGenerator::mockPluginDirectory(
            $this->plugin,
            [
                'Hooks' => [
                    "AddPreData.php" => "<?php\n\nnamespace App\Plugins\HookPlugin\Hooks;\n\nclass AddPreData\n{\npublic static function apply(array &\$data): void\n{\n\$data['hook-plugin-message'] = 'Executed';\n}\n}\n",
                ],
            ],
            "<hooks>\n<hook on=\"App\Http\Controllers\HomeController@getGlobalData\" handler=\"Hooks\\AddPreData@apply\" />\n</hooks>"
        );
    }

    protected function createPlugin()
    {
        $this->plugin          = new Plugin();
        $this->plugin->name    = 'HookPlugin';
        $this->plugin->uuid    = '123e4567-e89b-12d3-a456-426614174000';
        $this->plugin->version = '1.0.0';
        $this->plugin->save();
    }

    protected function tearDown(): void
    {
        $this->plugin->delete();
        parent::tearDown();
    }

}
