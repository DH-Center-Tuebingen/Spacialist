<?php

namespace Tests\Support;



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

    public function use(callable $callback, bool $skipTearDown = false): void {
        $this->setUp();
        try {
            $callback();
        } finally { 
            if(! $skipTearDown) {
            $this->tearDown();
            }
        }
    }

    protected function setUp(): void {
        foreach($this->templates as $template) {
            $plugin = $template->plugin;
            $additionalStructure = $template->getAdditionalStructure();
            $additionalInfoContent = $template->getAdditionalInfoContent();
            $this->directoriesToCleanup[] = PluginDirectoryGenerator::mockPluginDirectory($plugin, $additionalStructure, $additionalInfoContent);
            $plugin->save();
            $plugin->handleInstallation();
        }
    }

    protected function tearDown(): void {
        foreach($this->directoriesToCleanup as $dir) {
            PluginDirectoryGenerator::cleanup($dir);
        }
        $this->directoriesToCleanup = [];
    }
}