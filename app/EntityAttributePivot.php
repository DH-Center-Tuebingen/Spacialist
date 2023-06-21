<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EntityAttributePivot extends Pivot
{
    //

    protected $casts = [
        'depends_on' => 'array',
        'metadata' => 'array',
    ];
}
