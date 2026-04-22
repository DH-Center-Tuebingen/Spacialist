<?php

namespace Tests\Support;

use App\Plugin;

use Tests\Support\PluginXml;

class PluginTemplate {

    public Plugin $plugin;

    protected $structure = [];
    protected $features = [];
    public ?string $changelog = "[DEFAULT CHANGELOG]";
    public ?string $packageJson = null;
    private bool $generateCalled = false;


    public function __construct(
        string $name,
        string $uuid,
        string $version,
    ) {
        $this->plugin = new Plugin();
        $this->plugin->name = $name;
        $this->plugin->uuid = $uuid;
        $this->plugin->version = $version;
    }

    public function addFeature(PluginTemplateFeature $feature): static {
        $this->features[] = $feature;
        return $this;
    }

    public function changelog(?string $changelog = null): static {
        $this->changelog = $changelog;
        return $this;
    }

    public function packageJson(?string $packageJson = null): static {
        $this->packageJson = $packageJson;
        return $this;
    }



    public function addFile(string $filePath, string $fileContent): void {
        $filePath = str_replace('\\', '/', $filePath);
        $parts = explode('/', $filePath);
        $parts = array_filter($parts); // Remove empty parts

        if(count($parts) === 0) {
            throw new \Exception("Invalid file path: $filePath");
        }

        $fileName = array_pop($parts);
        $current = &$this->structure;
        foreach($parts as $part) {
            if(!isset($current[$part])) {
                $current[$part] = [];
            } else if(!is_array($current[$part])) {
                throw new \Exception("Path conflict: $filePath conflicts with existing file.");
            }
            $current = &$current[$part];
        }

        $current[$fileName] = $fileContent;
    }

    public function addBasic(): static {
        return $this->addBasicChangelog()
            ->addBasicJs();
    }

    public function addBasicJs(): static {
        $this->addFile("js/script.js", "console.log('Hello from {$this->plugin->name}');");
        return $this;
    }

    public function addBasicChangelog(): static {
        $this->changelog = "[DEFAULT CHANGELOG]";
        return $this;
    }

    /***
     * Generates the info.xml file based on the plugin's properties and hooks.
     */
    public function generate(?string $path = null): static {
        if($this->generateCalled) {
            throw new \Exception("generate() has already been called on this template instance. Please create a new instance to generate again.");
        }

        if($path === null) {
            $path = "App/info.xml";
        } else {
            $path = str_replace('\\', '/', $path);
        }

        $parts = explode('/', $path);
        if(count($parts) === 0) {
            throw new \Exception("Invalid file path: $path");
        }

        $fileName = array_pop($parts); // Remove file name

        $current = &$this->structure;
        while(count($parts) > 0) {
            $part = array_shift($parts);
            if(!isset($current[$part])) {
                $current[$part] = [];
            } else if(!is_array($current[$part])) {
                throw new \Exception("Path conflict: $path conflicts with existing file.");
            }
            $current = &$current[$part];
        }

        $additionalXml = "";
        foreach($this->features as $feature) {
            $additionalXml .= $feature->generateXml();
        }

        $current[$fileName] = PluginXml::generate($this, $additionalXml);
        return $this;
    }

    public function getStructure(): array {
        return $this->structure;
    }

    public function build(): static {
        return $this;
    }
}