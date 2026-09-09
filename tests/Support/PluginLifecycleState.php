<?php

namespace Tests\Support;


/**
 * Enum to dtermine current lifecyclestate of a plugin.
 */
enum PluginLifecycleState {
    case CREATED;
    case INSTALLED;
    case UNINSTALLED;
    case REMOVED;
}