<?php

namespace App\Services;

use App\Plugin;
use Illuminate\Support\Facades\Cache;

class AccessPointsService {
    private const CACHE_KEY = 'access_points';

    public const /*array*/ CORE_ACCESSPOINTS = [
        "Default" => [
            "label" => "main.user.accesspoints.default",
            "path" => "/",
        ],
    ];

    public function clearCache(): void {
        Cache::forget(self::CACHE_KEY);
    }

    public function get(): array {
        return Cache::rememberForever(self::CACHE_KEY, function() {
            $accesspoints = self::CORE_ACCESSPOINTS;
            $accesspoints = array_merge($accesspoints, self::loadAccessPointsFromPlugins());
            return $accesspoints;
        });
    }

    private function loadAccessPointsFromPlugins(): array {
        $installedPlugins = Plugin::getInstalled();
        $accesspoints = [];
        foreach($installedPlugins as $plugin) {
            $accesspoints = array_merge($accesspoints, $plugin->getAccessPoints());
        }
        return $accesspoints;
    }
}