<?php

namespace App\Services\Plugin;

use App\Plugin;
use App\Plugin\Manifest as PluginManifest;

/**
 * Base class for all PluginServices.
 * It provides the lifecycle methods for the operations:
 * + install,
 * + update
 * + uninstall
 * + remove.
 *
 * It provides several functions for each operation ###:
 * + ###            - Runs in normal installation loop. Normally the default you need.
 * + on###Before    - Allows for a function to run before all ### functions.
 * + on###After     - Allows for a function to run after all ### functions.
 * + on###Failed    - Runs after any service failed to be installed. Useful for cleaning up database unrelated operations that did not rollback on failure.
 * + on###Success   - Runs after all services executed correctly.
 *
 *
 * Philosophy:  When considering how to design your service, you should try to keep all the relevant data
 *              while the plugin is present.
 *              So that the user can disable (uninstall) a plugin, reenable (install)
 *              it and still have the same functionality, e.g. roles should be the same after disabling and enabling the plugin
 *              and the user should not be forced to reapply all roles again.
 *
 *              -> Upload: Plugin can be discovered.
 *              -> [First] Installation: [Data is written and] accessible.
 *              -> Uninstallation: Data is preserved, access is prohibited.
 *              -> Remove: Data is removed.
 */
abstract class PluginService {

    #region INSTALL

    /**
     * Invoked when the a Plugin is installed
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     *
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     * @return void
     */
    public function install(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Called when the installation was successful and the all services were applied.
     *
     * @return void
     */
    public function onInstallSuccess(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Called when the installation has failed and the database
     * was rolled back.
     *
     * Can be used to revert changes that are unrelated to the database
     * e.g. creating a file.
     * @return void
     */
    public function onInstallFailed(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked before a plugin is being installed
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     *
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onBeforeInstall(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked after a plugin has was installed
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onAfterInstall(Plugin $plugin, PluginManifest $manifest): void {}

    #endregion
    #region UPDATE

    /**
     * Invoked when the a Plugin is updated
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function update(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Called when the update has failed and the database
     * was rolled back.
     *
     * Can be used to revert changes that are unrelated to the database
     * e.g. creating a file.
     * @return void
     */
    public function onUpdateFailed(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Called when the update was successful and the all services were applied.
     *
     * @return void
     */
    public function onUpdateSuccess(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked before a Plugin is being updated
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onBeforeUpdate(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked after a plugin has was updated
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onAfterUpdate(Plugin $plugin, PluginManifest $manifest): void {}

    #endregion
    #region UNINSTALL

    /**
     * Invoked when the a Plugin is uninstalled
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Called when the uninstall has failed and the database
     * was rolled back.
     *
     * Can be used to revert changes that are unrelated to the database
     * e.g. creating a file.
     * @return void
     */
    public function onUninstallFailed(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Called when the uninstallation was successful and the all services were applied.
     *
     * @return void
     */
    public function onUninstallSuccess(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked before a Plugin is being uninstalled
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onBeforeUninstall(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked after a plugin has was uninstalled
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onAfterUninstall(Plugin $plugin, PluginManifest $manifest): void {}

    #endregion
    #region REMOVE
    /**
     * Invoked when the a Plugin is removed
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function remove(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Called when the remove has failed and the database
     * was rolled back.
     *
     * Can be used to revert changes that are unrelated to the database
     * e.g. creating a file.
     * @return void
     */
    public function onRemoveFailed(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Called when the removal was successful and the all services were applied.
     *
     * @return void
     */
    public function onRemoveSuccess(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked before a Plugin is being removed
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onBeforeRemove(Plugin $plugin, PluginManifest $manifest): void {}

    /**
     * Invoked after a plugin has was removed
     *
     * The PluginManager wrapps the function in a transaction,
     * so there doesn't need to be a separate transaction.
     * If a service fails to execute all service operations
     * will be reverted.
     * @param Plugin $plugin
     * @param PluginManifest $manifest
     */
    public function onAfterRemove(Plugin $plugin, PluginManifest $manifest): void {}

    #endregion
}