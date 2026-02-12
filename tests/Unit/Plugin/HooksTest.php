<?php
namespace Tests\Unit\Plugin;

use App\Models\Plugin\Hook;
use App\Services\HookService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Cases\Plugin\PluginHookTestCase;

class HooksTest extends PluginHookTestCase
{


    public function testAddHookSuccessfully()
    {
        $hookData = [
            'on'    => 'HomeController@getGlobalData',
            'src'   => 'Hooks\\AddPreData@apply',
            'order' => 1,
        ];

        $hookModel            = Hook::getHookFromJson($hookData, $this->plugin);
        $hookModel->plugin_id = $this->plugin->id;
        $hookModel->save();

        $this->assertDatabaseHas('plugin_hooks', [
            'plugin_id' => $this->plugin->id,
            'on'        => 'HomeController@getGlobalData',
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
        Hook::getHookFromJson($data, $this->plugin);
    }

    public function testAddHookToServiceSuccessfully() {
        $hookData = [
            'on' => 'HomeController@getGlobalData',
            'src' => 'Hooks\\AddPreData@apply',
            'order' => 1
        ];

        $hookService = new HookService();
        $hookService->addHook($hookData, $this->plugin);

        $this->assertDatabaseHas('plugin_hooks', [
            'plugin_id' => $this->plugin->id,
            'on' => 'HomeController@getGlobalData',
            'src' => 'Hooks\\AddPreData@apply',
            'order' => 1
        ]);
    }

    /**
     * Test addHook method of HookService class
     *
     * @return void
     */
    #[DataProvider('HookServiceExceptionProvider')]
    public function testAddHookServiceExceptions($data, $message)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($message);
        $hookService = new HookService();
        $hookService->addHook($data, $this->plugin);
    }

    public static function hookExceptionProvider()
    {
        return [
            "no fields"             => [[], "Hook is missing field(s): on, src"],
            "on only"               => [['on' => 'HomeController@getGlobalData'], "Hook is missing field(s): src"],
            "src only"              => [['src' => 'addPreData'], "Hook is missing field(s): on"],
            "src in invalid format" => [['on' => 'HomeController@getGlobalData', 'src' => 'addPreData'], "Hook 'src' field must be in the format 'class@method'"],
            "on is invalid hook"    => [['on' => 'Invalid@method', 'src' => 'NonExistentClass@method'], "Hook 'Invalid@method' is not a valid hook."],
        ];
    }

    public static function HookServiceExceptionProvider()
    {
        $hookExceptions        = static::hookExceptionProvider();
        $hookServiceExceptions = [
            "src class does not exist"  => [['on' => 'HomeController@getGlobalData', 'src' => 'Hooks\\NonExistentClass@method'], "Hook src 'Hooks\\NonExistentClass@method' does not exist in plugin 'HookPlugin', Class was resolved to: 'App\\Plugins\\HookPlugin\\Hooks\\NonExistentClass'"],
            "src method does not exist" => [['on' => 'HomeController@getGlobalData', 'src' => 'Hooks\\AddPreData@nonExistentMethod'], "Hook src 'Hooks\\AddPreData@nonExistentMethod' does not exist in plugin 'HookPlugin', Class was resolved to: 'App\\Plugins\\HookPlugin\\Hooks\\AddPreData'"],
        ];
        return array_merge($hookExceptions, $hookServiceExceptions);
    }
}
