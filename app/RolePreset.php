<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RolePreset extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    protected $appends = [
        'fullSet',
    ];

    protected $casts = [
        'rule_set' => 'array',
    ];

    public function plugin_presets() {
        return $this->hasMany('App\RolePresetPlugin', 'extends');
    }

    public function getFullSetAttribute() {
        $allRules = $this->rule_set;
        $pluginPresets = $this->plugin_presets;

        foreach($pluginPresets as $pluginPreset) {
            $allRules = array_merge($allRules, $pluginPreset->rule_set);
        }

        return $allRules;
    }
}
