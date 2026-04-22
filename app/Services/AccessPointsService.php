<?php

namespace App\Services;

use App\Services\PluginManager;

/**
 * Adds capability to publish custom CSS files to a plugin.
 * 
 * '''xml
 * ...
 * <accesspoint>
 *     <path>/path_to_access_point</path> <!-- full path e.g. https://spacialist.example.com/path_to_access_point -->
 *     <id>ExamplePluginId</id>
 *     <label>path.to.accesspoint.label</label>
 * </accesspoint>
 * ...
 * '''
 */
class AccessPointsService{

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