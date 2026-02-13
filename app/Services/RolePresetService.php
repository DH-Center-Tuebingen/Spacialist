<?php

namespace App\Services;

use App\Plugin;
use App\RolePreset;
use App\RolePresetPlugin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Service for managing role presets, including those defined by plugins.
 */
class RolePresetService extends CachedPluggableService
{   
    protected function getCacheKey(): string
    {
        return 'plugin_role_presets';
    }

    /**
     * Install role presets defined in a plugin's role-presets.json
     */
    public function install(Plugin $plugin): void
    {
        $rolePresets = $plugin->getRolePresets();

        DB::transaction(function() use ($rolePresets, $plugin) {
            foreach($rolePresets as $preset) {
                $baseRolePreset = RolePreset::where('name', $preset['extends'])->firstOrFail();
                $pluginPreset = new RolePresetPlugin();
                $pluginPreset->rule_set = $preset['rule_set'];
                $pluginPreset->extends = $baseRolePreset->id;
                $pluginPreset->from = $plugin->id;
                $pluginPreset->save();
            }
        });
    }

    /**
     * Remove all role presets that belong to a plugin
     */
    public function uninstall(Plugin $plugin): void
    {
        RolePresetPlugin::where('from', $plugin->id)->delete();
    }

    /***
     * Update role presets by uninstalling the old ones and installing the new ones.
     */
    public function update(Plugin $plugin): void
    {
        $this->uninstall($plugin);
        $this->install($plugin);
    }
    
    public function getRolePresetsForPlugin(Plugin $plugin): array
    {
        $pluginInfo = $plugin->getInfo();
        if(empty($pluginInfo['role_presets'] ?? [])) {
            return [];
        } 
        
        $presets = $pluginInfo['role_presets'];
        
        foreach($presets as &$preset) {
            if(empty($preset['extends'])) {
                throw new \Exception("Preset '".$preset['name']."' in plugin '".$plugin->name."' is missing 'extends' field");
            }
        
            $basePreset = RolePreset::where('name', $preset['extends'])->first();
            if($basePreset) {
                $preset['extends'] = $basePreset->id;
            } else {
                throw new \Exception("Base preset '".$preset['extends']."' not found for plugin '".$plugin->name."'");
            }
        }
        
        
    
        if(!File::isFile($rolePresets)) {
            return [];
        }

        return json_decode(file_get_contents($rolePresets), true);
    }

    public function remove(Plugin $plugin): void {}
}
