<?php
namespace Tests\Unit\Plugin;

use App\Services\Plugin\HookService;
use App\Plugin;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Assets\Templates\HookTemplate;
use Tests\Support\PluginGenerator;
use Tests\TestCase;

class HooksTest extends TestCase
{

    protected PluginGenerator $pluginGenerator;
    protected HookService $hookService;
    protected Plugin $plugin;

    public function setUp(): void
    {
        parent::setUp();
        $this->hookService = app(HookService::class);
        
        $hookTemplate = HookTemplate::getBasic()->generate();
        $this->plugin = $hookTemplate->plugin;
        $this->pluginGenerator = new PluginGenerator([$hookTemplate]);
        $this->pluginGenerator->setUp();
    }
    
    public function tearDown(): void
    {
        // $this->pluginGenerator->tearDown();
        parent::tearDown();
    }

    public function testAddHookSuccessfully()
    {
        $hookData = [
            'on'    => 'api/v1/pre',
            'src'   => 'Hooks\\AddPreData@apply',
            'order' => 1,
        ];

        $hookModel            = $this->hookService->createHookFromJson($hookData, $this->plugin);
        $hookModel->plugin_id = $this->plugin->id;
        $hookModel->save();

        $this->assertDatabaseHas('plugin_hooks', [
            'plugin_id' => $this->plugin->id,
            'on'        => 'api/v1/pre',
            'src'       => 'Hooks\\AddPreData@apply',
            'order'     => 1,
        ]);
    }

    /**
     * Test parseExport method of attributebase class
     *
     * @return void
     */
    #[DataProvider('hookExceptionProvider')]
     public function testAddHookExceptions($data, $message)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($message);
        $this->hookService->createHookFromJson($data, $this->plugin);
    }

    public function testAddHookToServiceSuccessfully()
    {
        $hookData = [
            'on'    => 'api/v1/pre',
            'src'   => 'Hooks\\AddPreData@apply',
            'order' => 1,
        ];

        $this->hookService->addHook($hookData, $this->plugin);

        $this->assertDatabaseHas('plugin_hooks', [
            'plugin_id' => $this->plugin->id,
            'on'        => 'api/v1/pre',
            'src'       => 'Hooks\\AddPreData@apply',
            'order'     => 1,
        ]);
    } 

    /**
     * Test addHook method of HookService class
     *
     * @return void
     */
    #[DataProvider('hookServiceExceptionProvider')]
    public function testAddHookServiceExceptions($data, $message)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($message);
        $this->hookService->addHook($data, $this->plugin);
    } 

    public static function hookExceptionProvider()
    {
        return [
            "no fields"             => [[], "Hook is missing field(s): on, src"],
            "on only"               => [['on' => 'api/v1/pre'], "Hook is missing field(s): src"],
            "src only"              => [['src' => 'addPreData'], "Hook is missing field(s): on"],
            "src in invalid format" => [['on' => 'api/v1/pre', 'src' => 'addPreData'], "Hook 'src' field must be in the format 'class@method'. Given: 'addPreData'"],
            "on is invalid hook"    => [['on' => 'invalid/api/route', 'src' => 'Hooks\\AddPreData@apply', 'method' => 'POST'], "Hook on invalid route 'POST::invalid/api/route'."],
            "on has invalid request method" => [['on' => 'api/v1/pre', 'src' => 'Hooks\\AddPreData@apply', 'method' => 'INVALID'], "Hook 'on' field has invalid method 'INVALID'. Allowed methods are GET, POST, PUT, DELETE, PATCH."],
            "on is forbidden hook"  => [['on' => 'broadcasting/auth', 'src' => 'Hooks\\AddPreData@apply', 'method' => 'POST'], "Hook on 'POST::broadcasting/auth' is not allowed."],
        ];
    }

    public static function hookServiceExceptionProvider()
    {
        $hookExceptions        = static::hookExceptionProvider();
        $hookServiceExceptions = [
            "src class does not exist"  => [['on' => 'api/v1/pre', 'src' => 'Hooks\\NonExistentClass@method'], "Hook src 'Hooks\\NonExistentClass@method' does not exist in plugin 'HookPlugin', Class was resolved to: 'App\\Plugins\\HookPlugin\\Hooks\\NonExistentClass'"],
            "src method does not exist" => [['on' => 'api/v1/pre', 'src' => 'Hooks\\AddPreData@nonExistentMethod'], "Hook src 'Hooks\\AddPreData@nonExistentMethod' does not exist in plugin 'HookPlugin', Class was resolved to: 'App\\Plugins\\HookPlugin\\Hooks\\AddPreData'"],
        ];
        return array_merge($hookExceptions, $hookServiceExceptions);
    }
}
