<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ThLanguage extends Model
{
    use LogsActivity;

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $logAttributes = ['id'];
    protected static $ignoreChangedAttributes = ['user_id'];

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
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function labels() {
        return $this->hasMany('App\ThConceptLabel', 'language_id');
    }
}
