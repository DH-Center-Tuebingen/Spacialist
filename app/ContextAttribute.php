<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContextAttribute extends Model
{
    protected $table = 'context_attributes';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context_type_id',
        'attribute_id',
    ];
}
