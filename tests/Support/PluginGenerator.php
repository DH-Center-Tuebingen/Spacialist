<?php

namespace Tests\Support;

use App\Plugin;
use App\Services\PluginManager;

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

    public function use(callable $callback, bool $skipTearDown = false): static {
        $this->setUp();
        try {
            $callback();
        } finally {
            if(!$skipTearDown) {
                $this->tearDown();
            }
        }
        return $this;
    }

    public function setUp(): void {
        foreach($this->templates as $template) {
            $plugin = clone ($template->plugin);
            $pluginDir = self::getPluginDirectory($plugin['name']);
            $this->directoriesToCleanup[] = PluginDirectoryGenerator::mockPluginDirectory($template, $pluginDir);
            $plugin->save();
            $template->plugin->id = $plugin->id; // Update the template's plugin ID to match the saved plugin
            $this->pluginMap[$plugin->name] = $plugin;

            if($template->isInstalled()) {
                app(PluginManager::class)->install($plugin);
                $this->overrideTimestamps($plugin, $template);
            }
        }
    }

    public function tearDown(): void {
        foreach($this->directoriesToCleanup as $dir) {
            PluginDirectoryGenerator::cleanup($dir);
        }
        $this->pluginMap = [];
        $this->directoriesToCleanup = [];
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
        info("Generated plugin contains the following templates:");
        foreach($this->templates as $template) {
            info("- " . $template->plugin->name);
            info("  - Files:");
            info(json_encode($template->getStructure(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            info("  - XML:");
            info(json_encode($template->getXml(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        info("End of plugin templates log.");
        info("================================");
    }
    
    public function getPlugin(string $name): ?Plugin {
        info(json_encode($this->pluginMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $this->pluginMap[$name] ?? null;
    }
}