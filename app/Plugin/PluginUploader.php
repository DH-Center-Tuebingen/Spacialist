<?php

namespace App\Plugin;

use App\Exceptions\HttpException;
use App\Plugin;
use Illuminate\Support\Str;
use App\Plugin\PluginDirectory;
use ZipArchive;

class PluginUploader {

    const mandatoryFiles = [
        'App/info.xml',
        'js/script.js',
        'routes/api.php',
    ];

    public function upload($file) {
        $zipFile = $this->tryOpenZipFile($file);
        $rootFolder = $this->retrieveRootDirectory($zipFile);
        // $this->validateMandatoryFiles($zipFile, $rootFolder);
        $this->validateInstalledVersionIsOlder($zipFile, $rootFolder);
        return $this->extractZipFile($zipFile, $pluginName);
    }


    private function tryOpenZipFile($file): ZipArchive {
        $zipFile = new ZipArchive();
        $isOpen = $zipFile->open($file->getRealPath(), ZipArchive::RDONLY);
        if($isOpen === true) {
            return $zipFile;
        } else {
            throw new HttpException(__('Could not open provided plugin zip file. Aborting.'), 403);
        }
    }

    private function retrieveRootDirectory(ZipArchive $zipFile): string {
        $rootFolder = $zipFile->getNameIndex(0);
        if(!Str::endsWith($rootFolder, '/')) {
            throw new HttpException(__('Format mismatch. Only a folder is allowed on root level.'), 403);
        }
        return $rootFolder;
    }

    private function validateMandatoryFiles(ZipArchive $zipFile, string $rootFolder) {

        //// Mandatory files are managed in PluginManager
        // $pluginName = substr($rootFolder, 0, -1);
        // foreach($mandatoryFiles as $filepath) {
        //     if($zipFile->locateName("{$rootFolder}{$filepath}") === false) {
        //         throw new HttpException(__('Format mismatch. Mandatory file :file is missing.', ['file' => "'{$rootFolder}{$filepath}'"]), 403);
        //     }
        // }
    }

    private function validateInstalledVersionIsOlder(ZipArchive $zipFile, string $pluginName) {

        $pluginPath = PluginDirectory::getPath($pluginName);

        if(file_exists($pluginPath)) {
            $installedPlugin = Plugin::where('name', $pluginName)->first();
            $manifest = PluginManifest::fromZip($zipFile);

            $existingVersion = $installedPlugin->version ?? '0.0.0';
            $uploadedVersion = $manifest->getVersion();

            if(version_compare($existingVersion, $uploadedVersion, ">=")) {
                return response()->json([
                    'error' => __("A plugin with the name ':pluginName' and the same or later version (:uploadedVersion and :existingVersion) already exists. Aborting.", [
                        'pluginName' => $pluginName,
                        'uploadedVersion' => $uploadedVersion,
                        'existingVersion' => $existingVersion,
                    ])
                ], 403);
            }
        }
    }

    private function extractZipFile(ZipArchive $zipFile, string $pluginName) {
        $extractPath = Str::finish(Plugin::getDirectoryPath($pluginName), '/');
        $extracted = $zipFile->extractTo($extractPath);
        $zipFile->close();

        if(!$extracted) {
            return response()->json([
                'error' => __("Error while extracting zip file. Please check file permissions or ask your system adminstrator.")
            ], 403);
        }
    }

    private function discoverExtractedPlugin(string $pluginName): Plugin {

        $plugin = Plugin::discoverPluginByName($pluginName);

        if(!isset($plugin)) {
            return response()->json([
                'error' => __("Error while reading from extracted content. Please check file permissions or ask your system adminstrator.")
            ], 403);
        }
    }

}