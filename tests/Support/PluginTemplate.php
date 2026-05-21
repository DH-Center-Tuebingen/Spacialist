<?php

namespace Tests\Support;

use App\Plugin;

use Tests\Support\PluginXml;

class PluginTemplate {

    public Plugin $plugin;
    protected $structure = [];
    protected array $xml = [];
    // protected $features = [];
    public ?string $changelog = "[DEFAULT CHANGELOG]";
    public ?string $packageJson = null;
    private bool $generateCalled = false;

    // We may to initialize a plugin in an uninstalled state.
    // having installed_at not set means, it should set on installation.
    // So with the installed property you can explicitly define the PluginTemplate
    // as uninstalled.
    private bool $installed;

    public function __construct(
        string $name,
        string $uuid,
        string $version,
        bool $installed = true,
        ?string $createdAt = null,
        ?string $installedAt = null,
        ?string $updatedAt = null,
        ?string $updateAvailable = null,
    ) {
        $this->plugin = new Plugin();
        $this->plugin->name = $name;
        $this->plugin->uuid = $uuid;
        $this->plugin->version = $version;
        $this->installed = $installed;

        if($updatedAt) {
            $this->plugin->updated_at = $updatedAt;
        }

        if($createdAt) {
            $this->plugin->created_at = $createdAt;
        }

        if($installedAt) {
            $this->plugin->installed_at = $installedAt;
        }

        if($updateAvailable !== null) {
            $this->plugin->update_available = $updateAvailable;
        }
    }

    public static function fromSlugArray(array $array): static {
        if(!isset($array['name']) || !isset($array['uuid']) || !isset($array['version'])) {
            throw new \Exception("Missing required fields 'name' or 'uuid' in array.");
        }

        return new static(
            name: $array['name'],
            uuid: $array['uuid'],
            version: $array['version'],
            updateAvailable: $array['update_available'] ?? null,
            createdAt: $array['created_at'] ?? null,
            installedAt: $array['installed_at'] ?? null,
            updatedAt: $array['updated_at'] ?? null,
        );
    }
    
    public function setChangelog(mixed $value): static {
        $this->changelog = $value;
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

    public function addFile(string $filePath, string $fileContent): static {
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
        return $this;
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

    public function addXml(string $rootTag, string $childTag, array $content): static {
        $this->xml[] = [
            "rootTag" => $rootTag,
            "childTag" => $childTag,
            "content" => $content
        ];
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
        foreach($this->xml as $xml) {
            $additionalXml .= $this->buildXml($xml['rootTag'], $xml['childTag'], $xml['content']);
        }

        $current[$fileName] = PluginXml::generate($this, $additionalXml);
        return $this;
    }

    private function buildXml(string $rootTag, string $childTag, array $content): string {
        $xmlContent = "    <$rootTag>\n";
        foreach($content as $key => $value) {
            $xmlContent .= "        <$childTag ";
            foreach($value as $attributeKey => $attributeValue) {
                $xmlContent .= "$attributeKey=\"$attributeValue\" ";
            }
            $xmlContent .= " />\n";
        }
        $xmlContent .= "    </$rootTag>";
        return $xmlContent;
    }

    public function getStructure(): array {
        if($this->changelog !== null) {
            $this->addFile("changelog.md", $this->changelog);
        }
        return $this->structure;
    }

    public function getXml(): array {
        return $this->xml;
    }

    public function build(): static {
        return $this;
    }

    public function setUninstalled():static {
        $this->installed = false;
        return $this;
    }
    
    public function isInstalled(): bool {
        return $this->installed;
    }
}