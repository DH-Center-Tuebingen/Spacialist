<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

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
        'lasteditor',
        'display_name',
        'short_name',
    ];

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $logAttributes = ['id'];
    protected static $ignoreChangedAttributes = ['lasteditor'];

    public function labels() {
        return $this->hasMany('App\ThConceptLabel', 'language_id');
    }
}
