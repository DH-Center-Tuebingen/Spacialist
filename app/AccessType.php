<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessType extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'accessible_id',
        'accessible_type',
        'type',
    ];

    /**
     * Get the owning accessible model.
     */
    public function accessible() {
        return $this->morphTo();
    }
}
