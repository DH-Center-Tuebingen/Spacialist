<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RolePresetPlugin extends Model
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
}
