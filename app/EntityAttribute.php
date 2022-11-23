<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EntityAttribute extends Model
{
    use LogsActivity;

    protected $table = 'entity_attributes';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_type_id',
        'attribute_id',
        'position',
        'depends_on',
    ];

    protected $casts = [
        'depends_on' => 'array',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }

    public function attribute() {
        return $this->belongsTo('App\Attribute');
    }

    public function entity_type() {
        return $this->belongsTo('App\EntityType');
    }
}
