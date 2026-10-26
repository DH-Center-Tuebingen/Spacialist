<?php

namespace Tests\Support\Plugin;

/**
 * Enum to dtermine current lifecyclestate of a plugin.
 */
enum LifecycleState {
    case CREATED;
    case INSTALLED;
    case UNINSTALLED;
    case REMOVED;
}