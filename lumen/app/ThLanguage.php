<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ThLanguage extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lasteditor',
        'display_name',
        'short_name',
    ];
}
