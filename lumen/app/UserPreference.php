<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    // disable primary key
    protected $primaryKey = null;
    public $incrementing = false;

    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
}
