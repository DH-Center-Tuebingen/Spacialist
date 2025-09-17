<?php

namespace App\Utils;

use App\Plugin;

class AccesspointUtils {
    public const array CORE_ACCESSPOINTS = [
        "Default" => [
            "label" => "main.user.accesspoints.default",
            "path" => "/",
        ],
    ];

    public static function get(): array {
        $accesspoints = self::CORE_ACCESSPOINTS;

        $installedPlugins = Plugin::getInstalled();
        foreach($installedPlugins as $plugin) {
            $accesspoints = array_merge($accesspoints, $plugin->getAccessPoints());
        }

        return $accesspoints;
    }
}