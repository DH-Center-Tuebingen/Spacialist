<?php

namespace Tests\Support\Plugin;

/*
 * Provides an easy to use lifecycle utility to set the
 * plugin state inside a test with a single command.
 */
class Lifecycle {
    static function RequiresInstall(LifecycleState $state): bool {
        switch($state){
            case LifecycleState::INSTALLED:
            case LifecycleState::UNINSTALLED:
            case LifecycleState::REMOVED:
                return true;
            default:
                return false;
        }
    }

    static function RequiresUninstall(LifecycleState $state): bool {
        switch($state){
            case LifecycleState::UNINSTALLED:
            case LifecycleState::REMOVED:
                return true;
            default:
                return false;
        }
    }

    static function RequiresRemove(LifecycleState $state): bool {
          return $state === LifecycleState::REMOVED;
    }
}