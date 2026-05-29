<?php
namespace Tests\Support;

use App\Plugin;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * This class provides helper methods to generate a plugin directory structure for testing purposes. 
 * It allows you to create a mock plugin directory with the necessary files and folders based on a 
 * given Plugin instance. The generated structure includes a package.json file, an info.xml file, and 
 * any additional files or directories specified in the input.
 */
class PluginDirectoryGenerator {
    /**
     */
    public static function cleanup(string $pluginDir): bool {
        if(file_exists($pluginDir) && is_dir($pluginDir)) {
            return File::deleteDirectory($pluginDir);
        } else {
            return false;
        }
    }


    /**
     * Mocks a zipped plugin for data upload.
     * 
     * @param PluginTemplate $template
     * @param string $zipFilePath
     * @return void
     */
    public static function mockPluginZipFile(PluginTemplate $template, string $zipFilePath): void {
        $tempDir = sys_get_temp_dir() . '/' . uniqid('plugin_zip_');
        mkdir($tempDir, 0755, true);
        $pluginDir = self::mockPluginDirectory($template, $tempDir . '/' . $template->plugin['name']);

        $zip = new ZipArchive();
        if($zip->open($zipFilePath, ZipArchive::CREATE) === true) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($pluginDir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            // We need to create the realpath of the tmpdir, on Windows the
            // path may be shortened, leading to a problem when counting the 
            // path length. This fixes that issue.
            $tempDirReal = realpath($tempDir) ?: $tempDir;
            $tempDirLength = strlen($tempDirReal) + 1; // +1 to account for the trailing slash
            foreach($files as $file) {
                if(!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, $tempDirLength);                    
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
        }else{
            throw new \Exception("Failed to create zip file at path: {$zipFilePath}");
        }

        // Clean up the temporary directory
        self::cleanup($tempDir);
    }

    /**
     * Mocks the plugin directory by creating the necessary files and folders based on the defined structure.
     * 
     * @param PluginTemplate $template The plugin template containing the structure and content for the plugin.
     * @param string $location The base location where the plugin directory should be created.
     * @return string The path to the created plugin directory.
     */
    public static function mockPluginDirectory(PluginTemplate $template, string $pluginDir): string {
        // Work on a clone so we don't mutate the caller's Plugin instance (array access
        // inside this helper may set attributes on the Eloquent model which causes
        // unexpected side effects such as trying to insert arrays into the DB).
        $pluginCopy = clone $template->plugin;

        $required_fields = ['name', 'uuid', 'version'];        
        $missing_fields = [];
        foreach($required_fields as $field) {
            if(!isset($pluginCopy[$field]) || empty($pluginCopy[$field])) {
                $missing_fields[] = $field;
            }
        }

        if(count($missing_fields) > 0) {
            throw new \Exception("Plugin is missing required fields: " . implode(", ", $missing_fields));
        }

        // Create plugin directory structure
        if(!file_exists($pluginDir)) {
            mkdir($pluginDir, 0755, true);
        }

        // Process structure array
        foreach($template->getStructure() as $name => $content) {
            $path = $pluginDir . '/' . $name;

            if(is_array($content)) {
                // Create directory and process contents
                if(!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                foreach($content as $subName => $subContent) {
                    if(is_int($subName) && is_string($subContent)) {
                        // Numeric key with string value means a filename placeholder
                        file_put_contents($path . '/' . $subContent, '');
                    } else {
                        file_put_contents($path . '/' . $subName, $subContent);
                    }
                }
            } else {
                // Create file with content
                $success = file_put_contents($path, $content);

                if(!$success) {
                    Log::error("Failed to create file at path: {$path}");
                    throw new \Exception("Failed to create file at path: {$path}");
                }
            }
        }

        return $pluginDir;
    }

    /**
     * Generates the content for the package.json file of the plugin based on the plugin's information.
     *
     * @return string The generated JSON content for the plugin's package.json file.
     */
    public static function generatePackageJSON(Plugin $plugin): string {
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




}


