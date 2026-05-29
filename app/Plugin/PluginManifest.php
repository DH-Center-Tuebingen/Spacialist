<?php

namespace App\Plugin;

use App\Plugin;
use App\Support\Log\PluginLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SimpleXMLElement;
use ZipArchive;

/**
 * Each plugin has a manifest file (plugin.xml) that contains metadata 
 * about the plugin, such as its name, version, author, description, 
 * and also requests specific permissions for the plugin, like custom 
 * components, preferences, Hooks, ... .
 * 
 * This utility class is responsible for parsing the plugin manifest 
 * and providing access to its contents. 
 */
class PluginManifest {

    public const MANIFEST_FILE_PATHS = [
        'plugin.xml',
        'App/info.xml',
    ];

    public function __construct(protected SimpleXMLElement $content) {
    }

    /**
     * Returns the text content of a leaf.
     * 
     * @param string $xpath - The xpath to a single leaf 
     * @return string
     */
    public function getTextContent(string $xpath): string {    
        $result = $this->content->xpath($xpath);
        if($result === false || count($result) === 0) {
            return "";
        }
        
        return (string) $result[0];
    }

    public function getVersion(): string {
        return $this->getTextContent('version') ?? '0.0.0';
    }

    public function getContent(): SimpleXMLElement {
        return $this->content;
    }

    public function getName(): string {
        return $this->getTextContent('name') ?? 'Unknown Plugin';
    }

    public function getDescription(): string {
        return $this->getTextContent('description') ?? '';
    }

    public function getLicence(): string {
        return $this->getTextContent('licence') ?? '';
    }

    public function getAuthors(): array {
        $authors = [];
        $authorNodes = $this->content->xpath('authors/author');
        if($authorNodes !== false) {
            foreach($authorNodes as $authorNode) {
                $authors[] = (string) $authorNode;
            }
        }
        return $authors;
    }

    public function getTagNodes(string $xpath): array {
        $children = [];
        $xmlNodeArray = $this->content->xpath($xpath);
        if(!$xmlNodeArray || count($xmlNodeArray) === 0) {
            return [];
        }


        foreach($xmlNodeArray as $xmlNode) {
            $tag = $xmlNode->getName();
            $attributes = [];
            foreach($xmlNode->attributes() as $attrName => $attrValue) {
                $attributes[$attrName] = (string) $attrValue;
            }

            $node = [
                "tag" => $tag,
                "text" => (string) $xmlNode,
                "attributes" => $attributes,
            ];


            $children[] = $node;
        }

        return $children;
    }

    // STATIC METHODS
    
    /**
     * Reads the manifest file, like the read() function
     * but obtains the plugin path by just the pluginName.
     * 
     * @param string $pluginName The name of the plugin (in PascalCase), which is also the name of the plugin directory.
     * @return bool| PluginManifest
     */
    public static function readFromName(string $pluginName): PluginManifest|false {
        $path = PluginDirectory::getPathByName($pluginName);
        return self::read($path);
    }

    /**
     * Tries to read a manifest file
     * 
     * @param mixed $path The absolute path to the plugin directory.
     * @return bool|PluginManifest
     */
    public static function read(string $path): PluginManifest|false {
        // TODO: Remove when releasing v1.0
        $manifest = false;
        foreach(self::MANIFEST_FILE_PATHS as $manifestFilePath) {
            $fullPath = Str::finish($path, '/') . $manifestFilePath;
            
            if(is_link($fullPath)) {
                $fullPath = readlink($fullPath);
            }

            if(File::isFile($fullPath)) {
                $xmlString = file_get_contents($fullPath);
                $manifest = self::parse($xmlString);

                if($manifest !== false) {
                    // TODO: Remove when releasing v1.0
                    if(static::isFilePathDeprecated($manifestFilePath)) {
                        $manifest->emitDeprecationWarning();
                    }
                    break;
                }
            }
        }

        return $manifest;
    }

    private function emitDeprecationWarning() {
        PluginLog::logWarning("Plugin '{$this->getName()}' is using a deprecated manifest file location. This will be not supported in future versions. Please move the manifest file to the root of the plugin directory and name it 'plugin.xml'.");
    }

    private static function isFilePathDeprecated(string $filePath): bool {
        return Str::startsWith($filePath, 'App/');
    }

    public static function parse(string $xmlString): PluginManifest|false {
        $xmlObject = simplexml_load_string($xmlString);

        if($xmlObject === false) {
            return false;
        }

        return new static($xmlObject);
    }

    public static function fromPlugin(Plugin $plugin): PluginManifest {
        $pluginPath = PluginDirectory::getPathByName($plugin->name);
        $manifest = self::read($pluginPath);
        if($manifest === false) {
            throw new \Exception("Plugin manifest not found for plugin: " . $plugin->name);
        }
        return $manifest;
    }

    public static function fromZip(ZipArchive $zip, ?string $pluginName = null): PluginManifest {
        foreach(self::MANIFEST_FILE_PATHS as $manifestFilePath) {
            if($pluginName) {
                $manifestFilePath = Str::finish($pluginName, '/') . $manifestFilePath;
            }            
            
            $manifestFilePath = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $manifestFilePath);   
            if($zip->locateName($manifestFilePath) !== false) {
                $xmlString = $zip->getFromName($manifestFilePath);
                $manifest = self::parse($xmlString);
                if($manifest !== false) {
                    // TODO: Remove when releasing v1.0
                    if(static::isFilePathDeprecated($manifestFilePath)) {
                        $manifest->emitDeprecationWarning();
                    }
                    return $manifest;
                }
            }
        }

        throw new \Exception("Plugin manifest not found in zip file.");
    }
}