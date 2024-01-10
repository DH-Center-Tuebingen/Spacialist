<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessRule extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'restrictable_id',
        'restrictable_type',
        'guardable_id',
        'guardable_type',
        'rule_type',
        'rule_values',
    ];

    protected $casts = [
        'rule_values' => 'array',
    ];

    /**
     * Get the owning restrictable model.
     */
    public function restrictable() {
        return $this->morphTo();
    }

    /**
     * Get the owning guardable model.
     */
    public function guardable() {
        return $this->morphTo();
    }
}
