<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContextPhoto extends Model
{
    protected $table = 'context_photos';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'photo_id',
        'context_id',
        'lasteditor',
    ];
}
