<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AvailableLayer extends Model
{
    use LogsActivity;

    protected $table = 'available_layers';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'type',
        'subdomains',
        'attribution',
        'opacity',
        'layers',
        'styles',
        'format',
        'version',
        'visible',
        'is_overlay',
        'api_key',
        'layer_type',
        'position',
        'color',
    ];

    const patchRules = [
        'name' => 'string',
        'type' => 'string',
        'url' => 'nullable|string',
        'subdomains' => 'nullable|string',
        'attribution' => 'nullable|string',
        'opacity' => 'between_float:0,1',
        'layers' => 'nullable|string',
        'styles' => 'nullable|string',
        'format' => 'nullable|string',
        'version' => 'nullable|numeric',
        'visible' => 'boolean_string',
        'is_overlay' => 'boolean_string',
        'api_key' => 'nullable|string',
        'layer_type' => 'nullable|string',
        'color' => 'color',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }

    public function patch($props) {
        // If updated baselayer's visibility is set to true, set all other base layer's visibility to false
        if(
            !$this->is_overlay &&
            !$this->visible &&
            array_key_exists('visible', $props) &&
            $props['visible']
        ) {
            $layers = self::where('is_overlay', false)
                ->where('id', '!=', $this->id)
                ->where('visible', true)
                ->update(['visible' => false]);
        }
        // only set props allowed in patch rules
        // and exclude 'visible' as it is not settable
        // (already defined in Illuminate\Database\Eloquent\Concerns\HidesAttributes)
        $supportedProps = Arr::except(
            Arr::only(
                $props,
                array_keys(self::patchRules)
            ),
            ['visible']
        );
        foreach($supportedProps as $key => $value) {
            $this->{$key} = $value;
        }
        if(array_key_exists('visible', $props)) {
            $this->update(['visible' => $props['visible']]);
        }
        $this->save();
    }

    public static function createFromArray($props) {
        $layer = new self();
        foreach($props as $k => $v) {
            $layer->{$k} = $v;
        }

        if(!\array_key_exists('is_overlay', $props)) {
            $props['is_overlay'] = true;
        }
        $layer->position = self::where('is_overlay', $props['is_overlay'])->max('position') + 1;
        $layer->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $layer->save();

        return self::find($layer->id);
    }

    public function entity_type() {
        return $this->belongsTo('App\EntityType');
    }
}
