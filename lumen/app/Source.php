<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'find_id',
        'attribute_id',
        'literature_id',
        'description',
        'lasteditor',
    ];
}
