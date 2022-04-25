<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EntityFile extends Model
{
    use LogsActivity;

    protected $table = 'entity_files';

    public $timestamps = false; // disable updated_at and created_at in ->save()

    // disable primary key
    protected $primaryKey = null;
    public $incrementing = false;

    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_id',
        'entity_id',
        'user_id',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['user_id'])
            ->logOnlyDirty();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }
}
