<?php

namespace App\Plugin;

use App\Plugin;
use Illuminate\Support\Str;
use App\Plugin\PluginDirectory;
use SplFileInfo;
use ZipArchive;

use function PHPUnit\Framework\directoryExists;

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
     * @return string - The name of the uploaded plugin
     */
    public function upload(SplFileInfo $file): string {
        $zipFile = $this->tryOpenZipFile($file);
        $pluginName = $this->retrieveRootDirectory($zipFile);

        if($this->isPluginAlreadyInstalled($pluginName)) {
            $this->validateInstalledVersionIsOlder($zipFile, $pluginName);
            $backupPath = $this->ensureBackupDirectoryExists();
            $this->removeExistingBackup($backupPath, $pluginName);
            $this->moveExistingPluginToBackup($backupPath, $pluginName);
        }

        $this->extractZipFile($zipFile, $pluginName);
        return $pluginName;
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
        $isOpen = $zipFile->open($file->getRealPath(), ZipArchive::RDONLY);
        if($isOpen === true) {
            return $zipFile;
        } else {
            abort(403, __('Could not open provided plugin zip file. Aborting.'));
        }
    }

    private function retrieveRootDirectory(ZipArchive $zipFile): string {
        $rootFolder = $zipFile->getNameIndex(0);
        if(!Str::endsWith($rootFolder, '/')) {
            abort(403, __('Format mismatch. Only a folder is allowed on root level.'));
        }
        return $rootFolder;
    }

    public function isPluginAlreadyInstalled(string $pluginName): bool {
        $pluginPath = PluginDirectory::getPath($pluginName);
        return file_exists($pluginPath);
    }

    private function validateInstalledVersionIsOlder(ZipArchive $zipFile, string $pluginName) {
        $installedPlugin = Plugin::where('name', $pluginName)->first();

        if(!isset($installedPlugin)) {
            return null;
        }

        $manifest = PluginManifest::fromZip($zipFile);
        $existingVersion = $installedPlugin->version ?? '0.0.0';
        $uploadedVersion = $manifest->getVersion();

        if(version_compare($existingVersion, $uploadedVersion, ">=")) {
            abort(403, __("A plugin with the name ':pluginName' and the same or later version (:uploadedVersion and :existingVersion) already exists. Aborting.", [
                'pluginName' => $pluginName,
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
        if(!directoryExists($backupDirectory)) {
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
        $extractPath = Str::finish(Plugin::getDirectoryPath($pluginName), '/');
        $extracted = $zipFile->extractTo($extractPath);
        $zipFile->close();

        if(!$extracted) {
            return abort(403, __("Error while extracting zip file. Please check file permissions or ask your system adminstrator."));
        }
    }
}