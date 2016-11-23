<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Find extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context_id',
        'root',
        'name',
        'lat',
        'lng',
        'lasteditor',
        'icon',
        'color',
    ];
}
