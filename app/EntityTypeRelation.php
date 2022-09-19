<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EntityTypeRelation extends Model
{
    use LogsActivity;

    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'child_id'
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }

    // TODO relations
}
