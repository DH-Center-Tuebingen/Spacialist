<?php

namespace Tests\Support;

use App\Plugin;
use App\Services\PluginManager;
use Illuminate\Support\Facades\Log;

/*
 * Generates plugin directories based on provided templates and ensures cleanup after tests.
 * Usage:
 *    $generator = new PluginGenerator([...]);
 *    $generator->use(function() {
 *        // Your test code here
 *    });
 */
class PluginGenerator {

    /**
     * Returns the full path to the plugin directory for a given plugin name.
     * NOTE: This is important for testing purposes: the plugin directory should be changed to a temporary 
     * location to avoid conflicts with real plugins.
     *
     * @return string The full path to the plugin directory.
     */
    public static function getPluginDirectory(string $pluginName): string {
        return base_path(config('app.plugin_directory')) . '/' . $pluginName;
    }


    private array $directoriesToCleanup = [];
    private array $pluginMap = [];

    public function __construct(private array $templates) {
    }

    public static function with(array $templates, callable $callback, bool $skipTearDown = false): static {
        return (new static($templates))->use($callback, $skipTearDown);
    }

    /**
     * Run's the generator lifecycle:
     * 
     * - Setup Plugin directory
     * - Executes callback function
     * - Removes the plugin from the filesystem and resets the generator state.
     * 
     * The filesystem structure can be maintained by passing skipTearDown.
     * Primarily interesting, for inspecting the generated template structure.
     * 
     * @param callable $callback
     * @param bool $skipTearDown
     * @return PluginGenerator
     */
    public function use(callable $callback, bool $skipTearDown = false): static {
        $this->setUp();
        try {
            $callback();
        } finally {
            $this->cleanUp($skipTearDown);
        }
        return $this;
    }

    /**
     * Cleans up the generator and the directories.
     * Directory cleanup can be skipped using $skiptTearDown;
     * 
     * @param bool $skipTearDown
     * @return void
     */
    public function cleanUp(bool $skipTearDown) {
        if(!$skipTearDown) {
            $this->tearDown();
        }

        $this->pluginMap = [];
        $this->directoriesToCleanup = [];
    }

    /**
     * Setup all templates according to their lifecycle state.
     * 
     * The state of all plugins will be changed in order of appearance
     * before continuing to the next step. E.g. when all templates are marked as UNINStALLED
     * all plugins will be first installed in order, and then uninstalled in the same order.
     * 
     * @return void
     */
    public function setUp(): void {
        $this->createTemplates();
        $this->installTemplates();
        $this->uninstallTemplates();
        $this->removeTemplates();
    }


    /**
     * Creates the provided template directories and stores the created plugins internally.
     * 
     * @return void
     */
    private function createTemplates() {
        foreach($this->templates as $template) {
            $plugin = clone ($template->plugin);
            $pluginDir = self::getPluginDirectory($plugin['name']);            $this->directoriesToCleanup[] = PluginDirectoryGenerator::mockPluginDirectory($template, $pluginDir);
            $plugin->save();
            $template->plugin->id = $plugin->id; // Update the template's plugin ID to match the saved plugin
            $this->pluginMap[$plugin->name] = $plugin;
        }
    }

    /**
     * Processes all templates and installs them if necessary.
     * 
     * @return void
     */
    private function installTemplates() {
        foreach($this->templates as $template) {
            if(PluginLifecycle::RequiresInstall($template->getLifecycleState())) {
                $plugin = $this->pluginMap[$template->plugin->name];
                app(PluginManager::class)->install($plugin);
                $this->overrideTimestamps($plugin, $template);
            }
        }
        
        // When we set up plugins but some are not installed,
        // we need to trigger a cache rebuild. 
        // In a real world scenario the cache should be rebuild
        // when the plugin is uploaded.
        app(PluginManager::class)->rebuildPluginCache();
    }

    /**
     * Processes all templates and uninstalls them if necessary.
     * 
     * @return void
     */
    private function uninstallTemplates() {
        foreach($this->templates as $template) {
            if(PluginLifecycle::RequiresUninstall($template->getLifecycleState())) {
                $plugin = $this->pluginMap[$template->plugin->name];
                app(PluginManager::class)->uninstall($plugin);
            }
        }
    }

    /**
     * Processes all templates and removes them if necessary.
     * 
     * @return void
     */
    private function removeTemplates() {
        foreach($this->templates as $template) {
            if(PluginLifecycle::RequiresRemove($template->getLifecycleState())) {
                $plugin = $this->pluginMap[$template->plugin->name];
                app(PluginManager::class)->remove($plugin);
            }
        }
    }

    /** */
    public function tearDown(): void {
        foreach($this->directoriesToCleanup as $dir) {
            PluginDirectoryGenerator::cleanup($dir);
        }
    }

    private function overrideTimestamps(Plugin $plugin, PluginTemplate $template): void {
        $timestampFields = ['created_at', 'updated_at', 'installed_at'];
        foreach($timestampFields as $field) {
            if($template->plugin->$field !== null) {
                $plugin->$field = $template->plugin->$field;
            }
        }
        $plugin->save();
    }

    public function logTemplates(): void {
        Log::notice("Generated plugin contains the following templates:");
        foreach($this->templates as $template) {
            Log::notice("- " . $template->plugin->name);
            Log::notice("  - Files:");
            Log::notice(json_encode($template->getStructure(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            Log::notice("  - XML:");
            Log::notice(json_encode($template->getXml(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        Log::notice("End of plugin templates log.");
        Log::notice("================================");
    }

    public function getPlugin(string $name): ?Plugin {
        return $this->pluginMap[$name] ?? null;
    }
}