<?php

namespace App\Services\Plugin;

use App\Plugin;

/**
 * Subclass for PluginServices. It provides the lifecycle methods: install, update, uninstall and remove.
 * Overwrite the functions your service needs to listen to.
 */
abstract class PluginService {

    /**
     * Invoked when the a Plugin is installed
     * @param Plugin $plugin
     * @return void
     */
    public function install(Plugin $plugin): void {
    }

    /**
     * Invoked before a plugin is being installed
     * @param Plugin $plugin
     * @return void
     */
    public function onBeforeInstall(Plugin $plugin): void {
    }

    /**
     * Invoked after a plugin has was installed
     * @param Plugin $plugin
     * @return void
     */
    public function onAfterInstall(Plugin $plugin): void {
    }


    /**
     * Invoked after a plugin has was updated
     * @param Plugin $plugin
     * @return void
     */
    public function onAfterUpdate(Plugin $plugin): void {
    }

    /**
     * Invoked before a Plugin is being updated
     * @param Plugin $plugin
     * @return void
     */
    public function onBeforeUpdate(Plugin $plugin): void {
    }

    /**
     * Invoked when the a Plugin is updated
     * @param Plugin $plugin
     * @return void
     */
    public function update(Plugin $plugin): void {
    }

    /**
     * Invoked when the a Plugin is uninstalled
     * @param Plugin $plugin
     * @return void
     */
    public function uninstall(Plugin $plugin): void {
    }

    /**
     * Invoked before a Plugin is being uninstalled
     * @param Plugin $plugin
     * @return void
     */
    public function onBeforeUninstall(Plugin $plugin): void {
    }

    /**
     * Invoked after a plugin has was uninstalled
     * @param Plugin $plugin
     * @return void
     */
    public function onAfterUninstall(Plugin $plugin): void {
    }

    /**
     * Invoked when the a Plugin is removed
     * @param Plugin $plugin
     * @return void
     */
    public function remove(Plugin $plugin): void {
    }

    /**
     * Invoked before a Plugin is being removed
     * @param Plugin $plugin
     * @return void
     */
    public function onBeforeRemove(Plugin $plugin): void {
    }

    /**
     * Invoked after a plugin has was removed
     * @param Plugin $plugin
     * @return void
     */
    public function onAfterRemove(Plugin $plugin): void {
    }
}