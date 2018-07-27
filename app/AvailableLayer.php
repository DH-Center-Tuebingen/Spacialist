<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AvailableLayer extends Model
{
    protected $table = 'available_layers';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [];

    const patchRules = [
        'name' => 'string',
        'url' => 'string',
        'type' => 'string',
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

    public function context_type() {
        return $this->belongsTo('App\ContextType');
    }
}
