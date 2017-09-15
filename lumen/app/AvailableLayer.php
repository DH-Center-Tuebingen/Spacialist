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
        'subdomains' => 'string',
        'attribution' => 'string',
        'opacity' => 'alpha_num',
        'layers' => 'string',
        'styles' => 'string',
        'format' => 'string',
        'version' => 'alpha_num',
        'visible' => 'boolean_string',
        'is_overlay' => 'boolean_string',
        'api_key' => 'string',
        'layer_type' => 'string',
        'color' => 'color',
    ];
}
