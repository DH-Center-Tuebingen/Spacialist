<?php
namespace Tests\Support;

use App\Plugin;

use Illuminate\Support\Facades\File;

class PluginDirectoryGenerator
{
    protected static array $createdPluginDir = [];

    /**
     * Deletes the created plugin directory if it exists. This method should be called after the tests are done to clean up the file system.
     */
    public static function cleanup(): void {
        if(!isset(static::$createdPluginDir[static::class])) {
            return;
        }
            
        $pluginDir = static::$createdPluginDir[static::class];
        if($pluginDir && file_exists($pluginDir) && is_dir($pluginDir)) {
            File::deleteDirectory($pluginDir);
            unset(static::$createdPluginDir[static::class]);
        }
    }

    /**
     * Creates the basic directory structure for a plugin.
     *
     * @param Plugin $plugin The plugin instance containing the necessary information to generate the directory structure.
     * @return array The directory structure as an associative array, where keys are file/directory
     *               names and values are either file content (string) or nested arrays for directories.
     */
    public static function defineStructure(Plugin $plugin, array $additionalStructure = [], string $additionalInfoContent = ''): array
    {
        $structure = [
            "package.json" => self::generatePackageJSON($plugin),
            "CHANGELOG.md" => "# Changelog",
            "js"           => [
                strtolower($plugin['name']) . "-" . $plugin['uuid'] . ".js" => "// Plugin main JS file",
            ],
            "App"          => [
                "info.xml" => self::generateInfoXml($plugin, $additionalInfoContent),
            ],
        ];

        $existingDirectories = ['js', 'App'];
        foreach($existingDirectories as $dir) {
            if(isset($additionalStructure[$dir])) {
                // If the content is an array we need to merge it with the existing structure.
                if(is_array($additionalStructure[$dir])) {
                    $structure[$dir] = array_merge($structure[$dir], $additionalStructure[$dir]);
                } else {
                    // If it's not an array, it's a file. We can just add it to the directory.
                    $structure[$dir][] = $additionalStructure[$dir];
                }
                // We unset the additional structure for this directory to avoid processing it again later.
                unset($additionalStructure[$dir]);
            }
        }

        return array_merge($structure, $additionalStructure);
    }

    /**
     * Returns the full path to the plugin directory for a given plugin name.
     *
     * @return string The full path to the plugin directory.
     */
    public static function getPluginDirectory(string $pluginName): string
    {
        return base_path(config('app.plugin_directory')) . '/' . $pluginName;
    }

    /**
     * Mocks the plugin directory by creating the necessary files and folders based on the defined structure.
     * This method runs once before the structure is cleaned up, to let it be called inside the setUp method of the test case
     * without the need for additional safeguards.
     * 
     * @param {array} $additionalStructure An associative array representing additional files or directories to be added to the plugin structure.
     * @param {string} $additionalInfoContent Additional XML content to be included in the info.xml file of the plugin.
     */
    public static function mockPluginDirectory(Plugin $plugin, array $additionalStructure = [], string $additionalInfoContent = ''): void
    {
        if(isset(self::$createdPluginDir[static::class])) {
            // When the directory has already been created, we should not create it again.
            return;
        }

        $required_fields = ['name', 'uuid', 'version'];

        $missing_fields = [];
        foreach($required_fields as $field) {
            if(! isset($plugin[$field]) || empty($plugin[$field])) {
                $missing_fields[] = $field;
            }
        }

        if(count($missing_fields) > 0) {
            throw new \Exception("Plugin is missing required fields: " . implode(", ", $missing_fields));
        }

        // Create plugin directory structure
        $pluginDir = self::getPluginDirectory($plugin['name']);
        if(! file_exists($pluginDir)) {
            mkdir($pluginDir, 0755, true);
        }

        $structure = self::defineStructure($plugin, $additionalStructure, $additionalInfoContent);
        // Process structure array
        foreach($structure as $name => $content) {
            $path = $pluginDir . '/' . $name;

            if(is_array($content)) {
                // Create directory and process contents
                if(! file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                foreach($content as $subName => $subContent) {
                    file_put_contents($path . '/' . $subName, $subContent);
                }
            } else {
                // Create file with content
                file_put_contents($path, $content);
            }
        }
        
        static::$createdPluginDir[static::class] = $pluginDir;
    }

    /**
     * Generates the content for the package.json file of the plugin based on the plugin's information.
     *
     * @return string The generated JSON content for the plugin's package.json file.
     */
    public static function generatePackageJSON(Plugin $plugin): string
    {
        return <<<JSON
{
    "name": "package_{$plugin['name']}",
    "pluginName": "{$plugin['name']}",
    "version": "{$plugin['version']}",
    "private": true,
    "type": "module"
}
JSON;
    }

    /**
     * Generates the content for the info.xml file of the plugin based on the plugin's information and any additional XML content provided.
     *
     * @return string The generated XML content for the plugin's info.xml file.
     */
    public static function generateInfoXml(Plugin $plugin, $additionalText = ""): string
    {
        $description = isset($plugin['description']) ? $plugin['description'] : "";
        $licence     = isset($plugin['licence']) ? $plugin['licence'] : "";
        $authors     = "";
        if(! isset($plugin["authors"])) {
            $plugin["authors"] = [];
        }

        foreach($plugin["authors"] as $author) {
            $authors .= "        <author>{$author}</author>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<info>
    <!-- Must match id in JS SpPS.register call in kebab-case -->
    <name>{$plugin['name']}</name>
    <title>{$plugin['name']} Plugin</title>
    <description>{$description}</description>
    <version>{$plugin['version']}</version>
    <licence>{$licence}</licence>
    <authors>{$authors}</authors>
    {$additionalText}
</info>
XML;
    }
}
