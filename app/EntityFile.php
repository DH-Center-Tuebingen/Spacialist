<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityFile extends Model
{
    protected $table = 'entity_files';

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
        'file_id',
        'entity_id',
        'lasteditor',
    ];
}
