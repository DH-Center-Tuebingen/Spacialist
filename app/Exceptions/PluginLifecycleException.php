<?php

namespace App\Exceptions;

use App\Enums\LifecycleOperation;
use App\Plugin;
use App\Support\Log\PluginLog;
use Exception;

/**
 * Throws an exception when an error occurs that is caused by an invalid plugin configuration. 
 * This can be used to catch errors during the plugin lifecycle, such as installation, activation, deactivation, and uninstallation,
 * especially inside the PluginService implementations.
 */
class PluginLifecycleException extends Exception {
    public function __construct(Plugin $plugin, string $message, public readonly ?LifecycleOperation $operation = null) {
        parent::__construct($message);
        PluginLog::for($plugin)->logError($message);
    }
}