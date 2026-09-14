<?php

namespace Tests\Support;

/*
 * Provides an easy to use lifecycle utility to set the 
 * plugin state inside a test with a single command.
 */
class PluginLifecycle {

    static function RequiresInstall(PluginLifecycleState $state): bool {
        switch($state){
            case PluginLifecycleState::INSTALLED:
            case PluginLifecycleState::UNINSTALLED:
            case PluginLifecycleState::REMOVED:
                return true;
            default:
                return false;
        }
    }

    static function RequiresUninstall(PluginLifecycleState $state): bool {
        switch($state){
            case PluginLifecycleState::UNINSTALLED:
            case PluginLifecycleState::REMOVED:
                return true;
            default:
                return false;
        }
    }

    static function RequiresRemove(PluginLifecycleState $state): bool {
          return $state === PluginLifecycleState::REMOVED;
    }
}