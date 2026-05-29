<?php

namespace App\Services;

use App\Services\PluginManager;

/**
 * 
 * Manages all acces points.
 * 
 * This combines the CORE_ACCESSPOINTS and the pluglin accespoints.
 */
class AccessPointsService {

    public const /*array*/ CORE_ACCESSPOINTS = [
        "Default" => [
            "label" => "main.user.accesspoints.default",
            "path" => "/",
        ],
    ];

    public function get(): array {
        $accesspoints = self::CORE_ACCESSPOINTS;
        $pluginAccessPoints = app(PluginManager::class)->accessPoints->getData();
        $accesspoints = array_merge($accesspoints, $pluginAccessPoints);
        return $accesspoints;
    }

}