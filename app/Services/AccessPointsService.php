<?php

namespace App\Services;

use App\Interfaces\IPluggable;
use App\Plugin;
use Exception;
use Illuminate\Support\Facades\Cache;

class AccessPointsService extends CachedService implements IPluggable{

    public const /*array*/ CORE_ACCESSPOINTS = [
        "Default" => [
            "label" => "main.user.accesspoints.default",
            "path" => "/",
        ],
    ];

    protected function getCacheKey(): string {
        return 'plugin_access_points';
    } 
      
    public function install(Plugin $plugin): void
    {
     throw new Exception("NOT IMPLEMENTED");
    }
    
    public function update(Plugin $plugin): void
    {
     throw new Exception("NOT IMPLEMENTED");
    }
    
    public function uninstall(Plugin $plugin): void
    {
     throw new Exception("NOT IMPLEMENTED");
    }
    
    public function remove(Plugin $plugin): void
    {
        // No separate remove logic needed for hooks
    }

    public function get(): array {
        return $this->updateCache(function () {
            $accesspoints = self::CORE_ACCESSPOINTS;
            $accesspoints = array_merge($accesspoints, $this->loadAccessPointsFromPlugins());
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