<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContextType extends Model
{
    protected $table = 'context_types';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'thesaurus_url',
        'type',
    ];
}
