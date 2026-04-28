<?php

namespace App\Support\Log;

use App\Plugin;
use Illuminate\Support\Facades\Log;

/*
 *  Should be overloaded inside a plugin to log with it's own name.
 * 
 * Log Levels as used in Laravel (RFC 5424)*:
 *  0       Emergency: system is unusable
 *  1       Alert: action must be taken immediately
 *  2       Critical: critical conditions
 *  3       Error: error conditions
 *  4       Warning: warning conditions
 *  5       Notice: normal but significant condition
 *  6       Informational: informational messages
 *  7       Debug: debug-level messages
 *
 * https://datatracker.ietf.org/doc/html/rfc5424
 */
class PluginLog extends Log {
    const CHANNEL_NAME = "plugin";

    public function __construct(private string $pluginName) {
    }

    protected function formatMessage(string $message, array $context = []): string {
        $pluginName = strtoupper($this->pluginName);
        return "[$pluginName] " . $message;
    }
    
    public static function fromPlugin(Plugin $plugin): PluginLog {
        return new PluginLog($plugin->name);
    }

    public function emergency(string $message, array $context = []): void {
        static::logEmergency($this->formatMessage($message), $context);
    }
    public function alert(string $message, array $context = []): void {
        static::logAlert($this->formatMessage($message), $context);
    }
    public function critical(string $message, array $context = []): void {
        static::logCritical($this->formatMessage($message), $context);
    }
    public function error(string $message, array $context = []): void {
        static::logError($this->formatMessage($message), $context);
    }
    public function warning(string $message, array $context = []): void {
        static::logWarning($this->formatMessage($message), $context);
    }
    public function notice(string $message, array $context = []): void {
        static::logNotice($this->formatMessage($message), $context);
    }
    public function info(string $message, array $context = []): void {
        static::logInfo($this->formatMessage($message), $context);
    }
    public function debug(string $message, array $context = []): void {
        static::logDebug($this->formatMessage($message), $context);
    }

    public static function logEmergency(string $message, array $context = []): void {
        Log::channel(self::CHANNEL_NAME)->emergency($message, $context);
    }
    public static function logAlert(string $message, array $context = []): void {
        Log::channel(self::CHANNEL_NAME)->alert($message, $context);
    }
    public static function logCritical(string $message, array $context = []): void {
        Log::channel(self::CHANNEL_NAME)->critical($message, $context);
    }
    public static function logError(string $message, array $context = []): void {
        Log::channel(self::CHANNEL_NAME)->error($message, $context);
    }
    public static function logWarning(string $message, array $context = []): void {
        Log::channel(self::CHANNEL_NAME)->warning($message, $context);
    }
    public static function logNotice(string $message, array $context = []): void {
        Log::channel(self::CHANNEL_NAME)->notice($message, $context);
    }
    public static function logInfo(string $message, array $context = []): void {
        Log::channel(self::CHANNEL_NAME)->info($message, $context);
    }
    public static function logDebug(string $message, array $context = []): void {
        Log::channel(self::CHANNEL_NAME)->debug($message, $context);
    }

}