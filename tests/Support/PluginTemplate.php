<?php

namespace Tests\Support;

use App\Plugin;

use Tests\Support\PluginXml;


/**
 * The PluginTemplate class is used for testing to replicate PluginTemplates
 * on the FileSystem to mimic the actual plugin functionality.
 * 
 * It works together with the PluginGenerator and PluginDirectoryGenerator
 * to create the database structure and also the physical structure on the filesystem.
 * 
 * Normally it's sufficient to work with the PluginTemplate directly,
 * but if you have a more complex use-case you may create a child class 
 * to mock templates more efficiently while keeping the test files tidy.  
 * 
 */
class PluginTemplate {

    public Plugin $plugin;
    private $manifest = "plugin.xml";
    protected $structure = [];
    protected array $xml = [];
    public ?string $changelog = null;
    public ?string $packageJson = null;
    private bool $generateCalled = false;
    private bool $skipInstallation = false;

    public function __construct(
        string $name,
        string $uuid,
        string $version,
        bool $skipInstallation = false,
        ?string $createdAt = null,
        ?string $installedAt = null,
        ?string $updatedAt = null,
        ?string $updateAvailable = null,
    ) {
        $this->plugin = new Plugin();
        $this->plugin->name = $name;
        $this->plugin->uuid = $uuid;
        $this->plugin->version = $version;
        $this->skipInstallation = $skipInstallation;

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

    /**
     * When we have an array describing the plugin similar to the
     * database representation of said plugin, we can generate it using
     * that slug named representation.
     * 
     * @param array $array - Plugin definition as an associated arrays, where keys are plugin properties in slug_case. Required keys: 'name', 'uuid', 'version'. Optional keys: 'update_available', 'created_at', 'installed_at', 'updated_at'.
     * @throws \Exception
     * @return $this
     */
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
    
    /**
     * Allows for setting the changelog afterwards.
     * This will overwrite an existing changelog.
     * 
     * @param mixed $value - The content of the changelog, if null the changelog will be removed.
     * @return $this - Returns the PluginTemplate instance for chaining
     */
    public function setChangelog(mixed $value): static {
        $this->changelog = $value;
        return $this;
    }
    
    /**
     * Sets the manifest file name, by default it's "plugin.xml".
     * 
     * @param string $manifest - The manifest file name, e.g. "custom_manifest.xml"
     * @return $this - Returns the PluginTemplate instance for chaining
     */
    public function setManifest(string $manifest): static {
        $this->manifest = $manifest;
        return $this;
    }
    
    /**
     * Sets the manifest file to the legacy "App/info.xml" path.
     * 
     * @return $this - Returns the PluginTemplate instance for chaining
     */
    public function setLegacyManifest(): static {
        $this->manifest = "App/info.xml";
        return $this;
    }

    /**
     * Sets the content of the package.json file.
     * This will overwrite an existing package.json content.
     * 
     * @param mixed $packageJson - The content of the package.json file as a string, if null the package.json file will not be created.
     * @return $this - Returns the PluginTemplate instance for chaining
     */
    public function packageJson(?string $packageJson = null): static {
        $this->packageJson = $packageJson;
        return $this;
    }

    /**
     * Add a filesystem file to the template.
     * This will be replicated on the file system when the plugin is generated.
     * 
     * @param string $filePath - The file path relative to the plugin root, e.g. "src/Example.php"
     * @param string $fileContent - The content of the file as a string
     * @throws \Exception if the file path is invalid or conflicts with an existing file/directory
     * @return $this - Returns the PluginTemplate instance for chaining
     */
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

    /**
     * Adds the basic features: changelog and js-script
     * 
     * @return $this
     */
    public function addBasic(): static {
        return $this->addBasicChangelog()
            ->addBasicJs();
    }

    /**
     * Adds the mandatory script.js file to the plugin installation with the default content:
     * ```js
     *      console.log('Hello from {PluginName}');
     * ```
     * 
     * 
     * @return $this
     */
    public function addBasicJs(): static {
        $this->addFile("js/script.js", "console.log('Hello from {$this->plugin->name}');");
        return $this;
    }

    /**
     * Adds the default changelog file with the content "[DEFAULT CHANGELOG]"
     * 
     * @return $this
     */
    public function addBasicChangelog(): static {
        $this->changelog = "[DEFAULT CHANGELOG]";
        return $this;
    }

    /**
     * Adds a custom xml node to the manifest file.
     * The XML should be kept simplistic and normally only has a maximum of 2 levels:
     * 
     * ```xml
     * <rootTag>
     *    <childTag attribute1="value1" attribute2="value2" />
     *    <childTag attribute1="value3" attribute2="value4" />
     *    ...
     * </rootTag>
     * <!-- or, if childTag is null -->
     * 
     * <rootTag attribute1="value1" attribute2="value2" />
     * <rootTag attribute1="value3" attribute2="value4" />
     * ```  
     * 
     * @param string $rootTag - The root tag name, e.g. "hooks"
     * @param string $childTag - The child tag name, e.g. "hook", can be set to null to only add the root tag with the provided content.
     * @param array $content - An array of child tag content, where each item is an associative array of attributes, e.g.: [["src" => "Hooks/Hook1@method", "on" => "api/v1/version" ],...]
     * @return $this - Returns the PluginTemplate instance for chaining
     */
    public function addXml(string $rootTag, string | null $childTag, array $content): static {
        $this->xml[] = [
            "rootTag" => $rootTag,
            "childTag" => $childTag,
            "content" => $content
        ];
        return $this;
    }

    /***
     * Generates the info.xml file based on the plugin's properties and hooks.
     * 
     * @param string|null $path Optional path for the info.xml file, defaults to "App/info.xml".
     * @return $this
     */
    public function generate(?string $path = null): static {
        if($this->generateCalled) {
            return $this;
        }
        $this->generateCalled = true;

        if($path === null) {
            $path = $this->manifest;
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

        $current[$fileName] = PluginXml::generate($this);
        return $this;
    }

    public function getStructure(): array {
        if($this->changelog !== null) {
            $this->addFile("CHANGELOG.md", $this->changelog);
        }
        return $this->structure;
    }

    /**
     * Returns the XML content.
     * 
     * @return array
     */
    public function getXml(): array {
        return $this->xml;
    }

    /**
     * Set the skipInstall if the plugin should be generated in an uninstalled
     * state, by default plugins are generated and installed directly.
     * 
     * @return $this
     */
    public function skipInstall():static {
        $this->skipInstallation = true;
        return $this;
    }
    /**
     * Sets the skipInstall property to false to install the plugin 
     * when generated.
     * 
     * Note: Normally this is the default behavior, but some implementation
     * may want to change the default to skipped, so we need to be able to
     * revert that in some cases. 
     * 
     * @return $this
     */
    public function install(): static {
        $this->skipInstallation = false;
        return $this;
    }
    
    /**
     * Check if the installation will be skipped.
     * 
     * @return bool
     */
    public function isSkippingInstall(): bool {
        return $this->skipInstallation;
    }
}