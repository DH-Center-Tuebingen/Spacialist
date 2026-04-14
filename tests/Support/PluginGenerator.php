<?php

namespace Tests\Support;

use App\Models\Plugin\Hook;
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

    private array $directoriesToCleanup = []; 

    public function __construct(private array $templates) {}

    public static function with(array $templates, callable $callback, bool $skipTearDown = false): static {
        return (new static($templates))->use($callback, $skipTearDown);
    }

    public function use(callable $callback, bool $skipTearDown = false): static {
        $this->setUp();
        try {
            $callback();
        } finally { 
            if(! $skipTearDown) {
                $this->tearDown();
            }
        }
        return $this;
    }

    public function setUp(): void {
        foreach($this->templates as $template) {
            $plugin = $template->plugin;
            $this->directoriesToCleanup[] = PluginDirectoryGenerator::mockPluginDirectory($template);
            $plugin->save();
            app(PluginManager::class)->install($plugin);
        }
    }

    public function tearDown(): void {
        foreach($this->directoriesToCleanup as $dir) {
            PluginDirectoryGenerator::cleanup($dir);
        }
        $this->directoriesToCleanup = [];
    }
}