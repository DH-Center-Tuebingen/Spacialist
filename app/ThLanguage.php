<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ThLanguage extends Model
{
    use LogsActivity;

    protected $table = 'th_language';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'display_name',
        'short_name',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['user_id'])
            ->logOnlyDirty();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function labels() {
        return $this->hasMany('App\ThConceptLabel', 'language_id');
    }
}
