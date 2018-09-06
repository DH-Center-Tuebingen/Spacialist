<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityAttribute extends Model
{
    protected $table = 'entity_attributes';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_type_id',
        'attribute_id',
    ];
}
