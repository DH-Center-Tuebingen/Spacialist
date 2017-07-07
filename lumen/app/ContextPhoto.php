<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContextPhoto extends Model
{
    protected $table = 'context_photos';

    public $timestamps = false; // disable updated_at and created_at in ->save()

    // disable primary key
    protected $primaryKey = null;
    public $incrementing = false;

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
