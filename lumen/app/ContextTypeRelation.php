<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContextTypeRelation extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'child_id'
    ];

    // TODO relations
}
