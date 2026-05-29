<?php

namespace App\Services\Plugin;

use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use App\RolePreset;
use App\RolePresetPlugin;
use App\Support\Log\PluginLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Service for managing role presets.
 * Role presets are additions to existing role presets. When a new role is generated
 * using an existing preset, the plugin functionality will be automatically be takein into account.
 * 
 * 
 * '''xml
 * ...
 *    <role-presets>
 *        <file src="/Path/To/presets.json" />
 *    </role-presets>
 * ...
 * '''
 */
class RolePresetService extends PluginService {

    /**
     * Install role presets defined in a plugin's role-presets.json
     */
    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $rolePresets = $this->getRolePresetsForPlugin($plugin, $manifest);        
        DB::transaction(function () use ($rolePresets, $plugin) {

            foreach($rolePresets as $preset) {

                if(!$preset["rule_set"]) {
                    PluginLog::for($plugin)->warning("Role preset entry in plugin '{$plugin->name}' is missing 'rule_set'. Skipping.");
                    continue;
                }

                if(!$preset["extends"]) {
                    PluginLog::for($plugin)->warning("Role preset entry in plugin '{$plugin->name}' is missing 'extends'. Skipping.");
                    continue;
                }

                $extends = $preset["extends"];
                $ruleSet = $preset["rule_set"];

                if(!is_array($ruleSet)) {
                    PluginLog::for($plugin)->warning("Role preset entry in plugin '{$plugin->name}' has 'rule_set' attribute that is not an array. Skipping.");
                    continue;
                }

                $baseRolePreset = RolePreset::where('name', $extends)->firstOrCreate(['name' => $extends]);
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
    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {
        RolePresetPlugin::where('from', $plugin->id)->delete();
    }

    /***
     * Update role presets by uninstalling the old ones and installing the new ones.
     */
    public function update(Plugin $plugin, PluginManifest $manifest): void {
        $this->uninstall($plugin, $manifest);
        $this->install($plugin, $manifest);
    }

    public function getRolePresetsForPlugin(Plugin $plugin, PluginManifest $manifest): array {
        $rolePresets = [];
        
        // TODO:: Remove in future version, until then we need to check if the deprecated path
        // is already defined in the manifest to avoid loading it twice and not logging the warning.
        $deprecatedRouteDefined = false;
        $rolePresetFiles = $manifest->getTagNodes('role-presets/file');
        foreach($rolePresetFiles as $roleFilePreset) {
            $src = $this->getPresetSrc($roleFilePreset, $plugin);
            if($src === null) {
                continue;
            }
            
            if($src === $this->getDeprecatedRoleFile()) {
                $deprecatedRouteDefined = true;
            }
            
            $parsedjson = $this->getRolePresetfromObject($roleFilePreset, $plugin);
            if($parsedjson !== null) {
                $rolePresets = array_merge($rolePresets, $parsedjson);
            }
        }
        
        // TODO:: Remove in future version.
        if(!$deprecatedRouteDefined) {
            $deprecatedPresets = $this->getDeprecatedRolePresets($plugin, $manifest);
            $rolePresets = array_merge($rolePresets, $deprecatedPresets);
        }

        return $rolePresets;
    }
    
    public function getDeprecatedRoleFile(){
        return "role-presets.json";
    }
    
    public function getDeprecatedRolePresets(Plugin $plugin, PluginManifest $manifest): array {
        $deprecatedFile = $this->getDeprecatedRoleFile();
        $directory = PluginDirectory::fromPlugin($plugin);
        $deprecatedFilePath = $directory->getAbsolutePluginPath($deprecatedFile);
        if(File::exists($deprecatedFilePath)) {
            $content = File::get($deprecatedFilePath);
            $parsedjson = json_decode($content, true);
            if($parsedjson === null) {
                PluginLog::for($plugin)->warning("Plugin '{$plugin->name}' has a 'role-presets.json' file that does not contain valid JSON. Skipping.\nJSON error: " . json_last_error_msg());
                return [];
            }
            PluginLog::for($plugin)->warning("Plugin '{$plugin->name}' is using the deprecated 'role-presets.json' file for role presets. Please migrate to using the 'role-presets/file' tag in the plugin manifest.");
            return $parsedjson;
        } else {
            return [];
        }
    }

    private function getPresetSrc(array $obj, Plugin $plugin): ?string{
        if(empty($obj['attributes']['src'])) {
            PluginLog::for($plugin)->warning("Plugin '{$plugin->name}' has a role preset entry without 'src' field. Skipping.");
            return null;
        }
        
        $pluginSrc = PluginDirectory::fromPlugin($plugin)->getAbsolutePluginPath($obj['attributes']['src']);
        if(!File::isFile($pluginSrc)) {
            PluginLog::for($plugin)->warning("Plugin '{$plugin->name}' has a role preset entry with 'src' field pointing to a non-existing file ('{$pluginSrc}'). Skipping.");
            return null;
        }

        return $pluginSrc;
    }
    
    private function getRolePresetfromObject(array $obj, Plugin $plugin): ?array {
        $pluginDirectory = PluginDirectory::fromPlugin($plugin);
        $pluginSrc = $pluginDirectory->getAbsolutePluginPath($obj['attributes']['src']);
        $parsedjson = json_decode(File::get($pluginSrc), true);
        if($parsedjson === null) {
            PluginLog::for($plugin)->warning("Plugin '{$plugin->name}' has a role preset entry with 'src' field pointing to a file ('{$pluginSrc}') that does not contain valid JSON. Skipping.\nJSON error: " . json_last_error_msg());
            return null;
        }

        return $parsedjson;
    }

}