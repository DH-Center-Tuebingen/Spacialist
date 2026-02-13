<?php

namespace Tests\Support;

class PluginGenerator {

    private array $directoriesToCleanup = []; 

    public function __construct(private array $templates) {}

    public function setUp() {
        foreach($this->templates as $template) {
            $plugin = $template->plugin;
            $additionalStructure = $template->getAdditionalStructure();
            $additionalInfoContent = $template->getAdditionalInfoContent();
            $this->directoriesToCleanup[] = PluginDirectoryGenerator::mockPluginDirectory($plugin, $additionalStructure, $additionalInfoContent);
            $plugin->save();
            $plugin->handleInstallation();
        }
    }

    public function tearDown() {
        foreach($this->directoriesToCleanup as $dir) {
            PluginDirectoryGenerator::cleanup($dir);
        }
        $this->directoriesToCleanup = [];
    }
}