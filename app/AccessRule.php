<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessRule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'objectable_id', 'objectable_type', 'group_id', 'rules',
    ];

    /**
     * Get the owning commentable model.
     */
    public function objectable() {
        return $this->morphTo();
    }

    public function group() {
        return $this->belongsTo('App\Group');
    }
}
