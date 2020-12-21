<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pref_id',
        'user_id',
        'value',
    ];

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $logAttributes = ['id'];
}
