<?php

namespace App\Services\Plugin;

use App\Plugin;
use App\RolePreset;
use App\RolePresetPlugin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Service for managing role presets.
 * 
 * 
 */
class RolePresetService extends PluginService
{   

    /**
     * Install role presets defined in a plugin's role-presets.json
     */
    public function install(Plugin $plugin): void
    {
        $rolePresets = $this->getRolePresetsForPlugin($plugin);
        DB::transaction(function() use ($rolePresets, $plugin) {

                foreach($rolePresets as $preset) {

                    if(!$preset["rule_set"]) {
                        info("Role preset entry in plugin '{$plugin->name}' is missing 'rule_set'. Skipping.");
                        continue;
                    }

                    if(!$preset["extends"]) {
                        info("Role preset entry in plugin '{$plugin->name}' is missing 'extends'. Skipping.");
                        continue;
                    }

                    $extends = $preset["extends"];
                    $ruleSet = $preset["rule_set"];

                    if(!is_array($ruleSet)) {
                        info("Role preset entry in plugin '{$plugin->name}' has 'rule_set' attribute that is not an array. Skipping.");
                        continue;   
                    }

                    $baseRolePreset = RolePreset::where('name', $extends)->firstOrCreate(['name'=> $extends]);
                    $pluginPreset = new RolePresetPlugin();
                    $pluginPreset->rule_set = $ruleSet;
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

        if(empty($pluginInfo['role-presets'])) {
            return [];
        }        
        $infoRolePresets = $pluginInfo['role-presets'];

        if(empty($infoRolePresets['role-preset'])) {
            return [];
        }

        $rolePresetFiles = $infoRolePresets['role-preset'];

        // When the XML contains  a single <role-preset> entry, it is parsed as an associative array instead of an array of arrays. 
        // We need to normalize it to always be an array of arrays for consistent processing.
        if(!empty($rolePresetFiles['@attributes'])) {
            $rolePresetFiles = [$rolePresetFiles];
        }

        $rolePresets = [];
        foreach($rolePresetFiles as $roleFilePreset) {
            $parsedjson = $this->getRolePresetfromObject($roleFilePreset, $plugin);
            if($parsedjson !== null) {
                $rolePresets = array_merge($rolePresets, $parsedjson);
            }
        }
        
        return $rolePresets;
    }

    public function remove(Plugin $plugin): void {}



    private function getRolePresetfromObject($obj, Plugin $plugin): ?array{
            if(empty($obj['@attributes']['src'])) {
                info("Plugin '{$plugin->name}' has a role preset entry without 'src' field. Skipping.");
                return null;
            }
            
            $src = $obj['@attributes']['src'];
            $pluginSrc = $plugin->getPath($src);

            if(!File::isFile($pluginSrc)) {
                info("Plugin '{$plugin->name}' has a role preset entry with 'src' field pointing to a non-existing file ('{$pluginSrc}'). Skipping.");
                return null;
            }
            
            $parsedjson = json_decode(File::get($pluginSrc), true);
            if($parsedjson === null) {
                info("Plugin '{$plugin->name}' has a role preset entry with 'src' field pointing to a file ('{$pluginSrc}') that does not contain valid JSON. Skipping.\nJSON error: " . json_last_error_msg());
                return null;
            }

            return $parsedjson;
    }

}