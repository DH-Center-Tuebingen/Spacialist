<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhPhoto extends Model
{
    protected $table = 'ph_photo';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'modified',
        'cameraname',
        'photographer_id',
        'created',
        'thumb',
        'orientation',
        'copyright',
        'description',
        'lasteditor',
    ];
}
