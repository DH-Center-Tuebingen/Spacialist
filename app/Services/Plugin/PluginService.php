<?php

namespace App\Services\Plugin;

use App\Plugin;
use App\Plugin\PluginManifest;

/**
 * Subclass for PluginServices. It provides the lifecycle methods: install, update, uninstall and remove.
 * Overwrite the functions your service needs to listen to.
 */
abstract class PluginService {

    /**
     * Invoked when the a Plugin is installed
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     * @return void
     */
    public function install(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked before a plugin is being installed
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onBeforeInstall(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked after a plugin has was installed
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onAfterInstall(Plugin $plugin, PluginManifest $manifest): void {}


    /**
     * Invoked after a plugin has was updated
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onAfterUpdate(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked before a Plugin is being updated
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onBeforeUpdate(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked when the a Plugin is updated
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function update(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked when the a Plugin is uninstalled
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked before a Plugin is being uninstalled
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onBeforeUninstall(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked after a plugin has was uninstalled
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onAfterUninstall(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked when the a Plugin is removed
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function remove(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked before a Plugin is being removed
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onBeforeRemove(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked after a plugin has was removed
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onAfterRemove(Plugin $plugin, PluginManifest $manifest): void {}
    
}