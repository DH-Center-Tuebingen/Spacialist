<?php

namespace App\Plugin;

use App\Plugin;
use App\Plugin\Support\PluginUploadResult;
use Illuminate\Support\Str;
use App\Plugin\PluginDirectory;
use SplFileInfo;
use ZipArchive;

class PluginUploader {

    /**
     * Uploads a plugin zip file, extracts it to the plugin directory, 
     * and returns the name of the uploaded plugin.
     * 
     * The zip file must contain a single root directory with the same name
     * as the plugin. 
     * 
     * If a plugin with the same name already exists, 
     * it is moved to a backup directory before extracting the new plugin. 
     * The backup directory is located at "plugins/_backups" and is created if it does not exist.
     * If a backup of the same plugin already exists in the backup directory, 
     * it is removed before moving the existing plugin to the backup directory. 
     *  
     * @param SplFileInfo $file - The uploaded plugin zip file
     * @return PluginUploadResult - The result of the upload, containing the plugin name and whether the pluginw as updated or created.
     */
    public function upload(SplFileInfo $file): PluginUploadResult {
        $zipFile = $this->tryOpenZipFile($file);
        $pluginName = $this->getSingleRootDirectory($zipFile);
        $plugin = Plugin::where('name', $pluginName)->first();
        
        if(isset($plugin)) {
            $this->validateExistingVersionIsOlder($zipFile, $plugin);
        }

        if($this->doesPluginDirectoryExist($pluginName)) {
            $backupPath = $this->ensureBackupDirectoryExists();
            $this->removeExistingBackup($backupPath, $pluginName);
            $this->moveExistingPluginToBackup($backupPath, $pluginName);
        }

        $this->extractZipFile($zipFile, $pluginName);
        return new PluginUploadResult($pluginName, $plugin);
    }


    /**
     * Restores an existing backup to the plugin folder.
     * 
     * @param string $pluginName
     * @return bool - Returns true if the backup was successfully restored, false if no backup exists for the given plugin name.
     */
    public function restoreBackup(string $pluginName): bool {
        $backupPath = PluginDirectory::getPath("_backups/{$pluginName}");
        if(!file_exists($backupPath)) {
            return false;
        }

        $existingPluginPath = PluginDirectory::getPath($pluginName);
        if(file_exists($existingPluginPath)) {
            sp_remove_dir($existingPluginPath);
        }

        rename($backupPath, $existingPluginPath);
        return true;
    }

    private function tryOpenZipFile(SplFileInfo $file): ZipArchive {
        $zipFile = new ZipArchive();
        info($file->getRealPath());
        $isOpen = $zipFile->open($file->getRealPath(), ZipArchive::RDONLY);
        if($isOpen === true) {
            return $zipFile;
        } else {
            abort(403, __('Could not open provided plugin zip file. Aborting.'));
        }
    }
    
    /**
     * Checks if the archive has a single root directory. 
     * This directory dictates the name of the uploaded plugin.
     * That's how the plugin system knows what plugin is getting
     * updated.
     * 
     * Note: Retrieving the root directory is unnecessarily expensive
     * as we cannot assume, that the first entry is the root directory.
     * 
     * @param ZipArchive $zipFile
     * @return string
     */
    private function getSingleRootDirectory(ZipArchive $zipFile): string {
        $foundRoot = null;
        $num = $zipFile->numFiles;

        // We need to check all entries of the zip to ensure that there is exactly one root directory
        // all other options are unreliable.
        for($i = 0; $i < $num; $i++) {
            $name = $zipFile->getNameIndex($i);
            if($name === false) {
                continue;
            }
            // Replace backslashes with forward slashes and remove leading slashes or dots
            $name = preg_replace('#^(\./|/)+#', '', str_replace('\\', '/', $name));
            if($name === '') {
                continue;
            }

            // Ignore __MACOSX folder, which is sometimes added by macOS when creating zip files.
            if(strpos($name, '__MACOSX/') === 0) {
                continue;
            }

            $root = explode('/', $name, 2)[0];

            // If root is empty after removing leading slashes and dots, skip it.
            if(!trim($root)) {
                continue;
            }

            if($foundRoot != null && $foundRoot != $root) {
                abort(403, __('Format mismatch. Archive must contain exactly one root folder.'));
            } else {
                $foundRoot = $root;
            }
        }

        if($foundRoot === null) {
            abort(403, __('Could not find root directory in zip file. Aborting.'));
        }

        // Foundroot should always be without trailing slashes.
        return $foundRoot;
    }
    
    public function doesPluginDirectoryExist(string $pluginName): bool {
        $pluginPath = PluginDirectory::getPath($pluginName);
        return file_exists($pluginPath);
    }

    private function validateExistingVersionIsOlder(ZipArchive $zipFile, Plugin $existingPlugin): void {

        if(!isset($existingPlugin)) {
            return;
        }

        $manifest = PluginManifest::fromZip($zipFile, $existingPlugin->name);
        $existingVersion = $existingPlugin->version ?? '0.0.0';
        $uploadedVersion = $manifest->getVersion();

        if(version_compare($existingVersion, $uploadedVersion, ">=")) {
            abort(403, __("A plugin with the name ':pluginName' and the same or later version (:uploadedVersion and :existingVersion) already exists. Aborting.", [
                'pluginName' => $existingPlugin->name,
                'uploadedVersion' => $uploadedVersion,
                'existingVersion' => $existingVersion,
            ]));
        }
    }

    private function moveExistingPluginToBackup(string $backupPath, string $pluginName) {
        $existingPluginPath = PluginDirectory::getPath($pluginName);
        $backupPluginPath = $backupPath . '/' . $pluginName;



        if(!file_exists($existingPluginPath)) {
            return null;
        }

        rename($existingPluginPath, $backupPluginPath);
    }

    private function ensureBackupDirectoryExists(): string {
        $backupDirectory = PluginDirectory::getPath("_backups");
        if(!is_dir($backupDirectory)) {
            mkdir($backupDirectory, 0755, true);
        }

        return $backupDirectory;
    }

    private function removeExistingBackup(string $backupPath, string $pluginName): void {
        $existingBackupPath = $backupPath . '/' . $pluginName;
        if(file_exists($existingBackupPath)) {
            sp_remove_dir($existingBackupPath);
        }
    }

    private function extractZipFile(ZipArchive $zipFile, string $pluginName) {
        // As the zip file contains a single root directory with the same name as the plugin, 
        // we can safely extract it directly to the plugin directory.
        $pluginDirectory = PluginDirectory::getPath();
        $extractPath = Str::finish($pluginDirectory, '/');

        $extracted = $zipFile->extractTo($extractPath);
        $zipFile->close();

        if(!$extracted) {
            return abort(403, __("Error while extracting zip file. Please check file permissions or ask your system adminstrator."));
        }
    }
}