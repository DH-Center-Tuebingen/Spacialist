<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContextValue extends Model
{
    protected $table = 'context_values';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context_id',
        'attribute_id',
        'context_val',
        'str_val',
        'int_val',
        'dbl_val',
        'dt_val',
        'possibility',
        'lasteditor',
        'thesaurus_val',
    ];
}
