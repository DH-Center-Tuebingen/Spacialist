<?php

namespace App\Plugin;

use App\Plugin;
use App\Services\PluginManager;
use App\Support\Log\PluginLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Each plugin has a manifest file (plugin.xml) that contains metadata about the plugin, such as its name, version, author, description, 
 * and also requests specific permissions for the plugin, like custom components, preferences, Hooks, ... .
 * 
 * This utility class is responsible for parsing the plugin manifest and providing access to its contents. 
 */
class PluginManifest {

    public const MANIFEST_FILE_PATHS = [
        'plugin.xml',
        'App/info.xml',
    ];

    protected $metadataStringFields = [
        'description',
        'licence',
        'title',
        'version',
    ];

    public function __construct(protected array $content) {
    }
    
    public function getVersion(): string {
        return $this->content['version'] ?? '0.0.0';
    }

    public function getContent(): array {
        return $this->content;
    }

    public function getName(): string {
        return $this->content['name'] ?? 'Unknown Plugin';
    }

    public function getMetadata(): array {
        $metadata = [];
        foreach($this->metadataStringFields as $field) {
            if(array_key_exists($field, $this->content)) {
                $metadata[$field] = $this->content[$field];
            } else {
                $metadata[$field] = "";
            }
        }

        if(array_key_exists('authors', $this->content)) {
            if(array_key_exists('author', $this->content[$field])) {
                $authors = $this->content[$field]['author'];
                $metadata[$field] = is_array($authors) ? $authors : [$authors];
            } else {
                $metadata[$field] = [];
            }
        } else {
            $metadata['authors'] = [];
        }

        return $metadata;
    }

    // public function getAccessPoints(): array {
    //     $accesspoints = [];
    //     $addedNames = [];
    //     $addedPaths = [];
    //     if(array_key_exists('accesspoints', $this->content)) {
    //         foreach($this->content['accesspoints'] as $accesspoint) {
    //             $name = $this->getName() . '-' . $accesspoint['id'];
    //             $label = $accesspoint['label'];
    //             $path = Str::finish(Str::start($accesspoint['path'], '/'), '/');
    //             if(array_key_exists($name, $addedNames)) {
    //                 throw new \Exception("An accesspoint with the name ($name) already exists");
    //             }
    //             if(array_key_exists($path, $addedPaths)) {
    //                 throw new \Exception("An accesspoint with the path ($path) already exists");
    //             }

    //             $addedNames[$name] = true;
    //             $addedPaths[$path] = true;

    //             $accesspoints[$name] = [
    //                 'label' => $label,
    //                 'path' => $path,
    //             ];
    //         }
    //     }
    //     return $accesspoints;
    // }

    public function getAttributes(): array {
        $attributes = [];
        if(array_key_exists('attributes', $this->content)) {

            // When an empty <attributes> tag is provided, it is possible that the 'attribute' key does not exist in the $this->content array.
            // In this case, we return an empty array.
            if(!array_key_exists('attribute', $this->content['attributes'])) {
                return [];
            }

            $attributes = $this->content['attributes']['attribute'];
            // If only one <attribute> exists, this <attribute> is returned
            // instead of an array, but we always want an array
            if(array_key_exists('@attributes', $attributes)) {
                $attributes = [$attributes];
            }
        }
        return $attributes;
    }

    public function warn($message) {
        if(!isset($this->content['warnings'])) {
            $this->content['warnings'] = [];
        }

        $this->content['warnings'][] = $message;
    }



    // STATIC METHODS

    /**
     * Tries to read a manifest file
     * 
     * @param mixed $pluginDirectoryName The name of the plugin directory to read the manifest from
     * @return bool|Plugin\PluginManifest
     */
    public static function read(string $pluginDirectoryName): PluginManifest|false {
        // TODO: Remove when releasing v1.0
        $manifest = false;

        foreach(self::MANIFEST_FILE_PATHS as $manifestFilePath) {
            $fullPath = Str::finish($pluginDirectoryName, '/') . $manifestFilePath;
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

    private function emitDeprecationWarning(){
        $this->warn("Uses deprecated manifest file location.");
        PluginLog::logWarning("Plugin '{$this->getName()}' is using a deprecated manifest file location. This will be not supported in future versions. Please move the manifest file to the root of the plugin directory and name it 'plugin.xml'.");            
    }
    
    private static function isFilePathDeprecated(string $filePath): bool {
        return Str::startsWith($filePath, 'App/');
    }
    
    public static function parse(string $xmlString): static {
        $xmlObject = simplexml_load_string($xmlString);
        $xmlObject = json_decode(json_encode($xmlObject), true);
        return new self($xmlObject);
    }

    public static function fromPlugin(Plugin $plugin): PluginManifest {
        $pluginPath = PluginDirectory::getPathByName($plugin->name);
        $manifest = self::read($pluginPath);
        if($manifest === false) {
            throw new \Exception("Plugin manifest not found for plugin: " . $plugin->name);
        }
        return $manifest;
    }
    
    public static function fromZip(\ZipArchive $zip): PluginManifest {
        foreach(self::MANIFEST_FILE_PATHS as $manifestFilePath) {
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