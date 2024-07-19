<?php

namespace App\PluginResources\Presets;

use App\PluginResources\Interfaces\Installable;
use App\PluginResources\Plugin;
use App\RolePreset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class RolePresetPlugin extends Model implements Installable
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    protected $casts = [
        'rule_set' => 'array',
    ];
    
    public static function getRolePresets(Plugin $plugin) {
        $rolePresets = $plugin->getPath(["App", "role-presets.json"]);
        if(!File::isFile($rolePresets)) {
            return [];
        }

        return json_decode(file_get_contents($rolePresets), true);
    }
    
    public static function uninstall($plugin){
        self::where('from', $plugin->id)->delete();
    }
    
    public static function install($plugin){
        $rolePresets = self::getRolePresets($plugin);
        foreach($rolePresets as $preset) {
            $baseRolePreset = RolePreset::where('name', $preset['extends'])->firstOrFail();
            $pluginPreset = new RolePresetPlugin();
            $pluginPreset->rule_set = $preset['rule_set'];
            $pluginPreset->extends = $baseRolePreset->id;
            $pluginPreset->from = $plugin->id;
            $pluginPreset->save();
        }
    }
    
}
