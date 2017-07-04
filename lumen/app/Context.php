<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Context extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context_type_id',
        'root_context_id',
        'name',
        'lasteditor',
        'geodata_id',
    ];

    const rules = [
        'name'              => 'required|string',
        'context_type_id'   => 'required|integer|exists:context_types,id',
        'root_context_id'   => 'integer|exists:contexts,id',
        'geodata_id'        => 'integer|exists:geodata,id'
    ];

    const patchRules = [
        'name'              => 'string',
        'context_type_id'   => 'integer|exists:context_types,id',
        'root_context_id'   => 'integer|exists:contexts,id',
        'geodata_id'        => 'integer|exists:geodata,id'
    ];
}
